<?php

declare(strict_types=1);

namespace App\Radio\AutoDJ;

use App\Container\EntityManagerAwareTrait;
use App\Container\LoggerAwareTrait;
use App\Entity\Advertisement;
use App\Entity\Enums\AdMediaType;
use App\Entity\Repository\AdvertisementRepository;
use App\Entity\Song;
use App\Entity\Station;
use App\Entity\StationQueue;
use App\Event\Radio\BuildQueue;
use Psr\SimpleCache\CacheInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Inserts advertisements into the AutoDJ queue based on play_frequency.
 * 
 * This subscriber runs at priority 3 (between requests at 5 and normal songs at 0).
 * It tracks how many songs have played since the last ad for each station using Redis cache.
 * When the counter reaches an ad's play_frequency, it injects the ad into the queue.
 * 
 * Audio ads: Injected directly into the Liquidsoap queue (replaces the next song).
 * Video ads: A cache flag is set so the frontend overlay knows to display the video.
 *            The normal song continues playing in the audio stream.
 */
final class AdQueueBuilder implements EventSubscriberInterface
{
    use LoggerAwareTrait;
    use EntityManagerAwareTrait;

    private const string CACHE_PREFIX = 'ad_song_counter_';
    private const string VIDEO_AD_CACHE_PREFIX = 'station_current_ad_';

    public function __construct(
        private readonly AdvertisementRepository $adRepo,
        private readonly CacheInterface $cache,
    ) {
    }

    /**
     * @inheritDoc
     */
    public static function getSubscribedEvents(): array
    {
        return [
            BuildQueue::class => [
                ['insertAdvertisement', 3],
            ],
        ];
    }

    /**
     * Check if it's time to insert an ad and do so if needed.
     */
    public function insertAdvertisement(BuildQueue $event): void
    {
        // Don't insert ads in interrupting queue (that's for urgent content)
        if ($event->isInterrupting()) {
            return;
        }

        $station = $event->getStation();
        
        // Get the song counter for this station
        $songCounter = $this->getSongCounter($station);
        
        // Get the best ad to play for this station
        $ad = $this->adRepo->getBestAdForStation($station);
        
        if (null === $ad) {
            $this->logger->debug('No playable ads found for station.', [
                'station_id' => $station->id,
            ]);
            return;
        }

        // Check if we've played enough songs to trigger an ad
        if ($songCounter < $ad->play_frequency) {
            $this->logger->debug('Not yet time for ad.', [
                'station_id' => $station->id,
                'songs_since_last_ad' => $songCounter,
                'play_frequency' => $ad->play_frequency,
            ]);
            // Increment counter for next song
            $this->incrementSongCounter($station);
            return;
        }

        // Time to play an ad!
        $this->logger->info('Inserting advertisement into queue.', [
            'station_id' => $station->id,
            'ad_id' => $ad->id,
            'ad_name' => $ad->name,
            'ad_media_type' => $ad->media_type->value,
            'songs_since_last_ad' => $songCounter,
        ]);

        if ($ad->media_type === AdMediaType::Video) {
            // Video ads: Set cache flag for frontend overlay, don't interrupt audio stream
            $this->setVideoAdCache($station, $ad);
            
            // Reset the song counter
            $this->resetSongCounter($station);
            
            // Record the ad play
            $this->adRepo->recordAdPlay($ad);
            
            // Don't stop propagation — let normal song play while video overlays
            return;
        }

        // Audio ads: Inject into Liquidsoap queue
        $queueRow = $this->createQueueFromAd($station, $ad);
        
        if (null !== $queueRow) {
            $event->setNextSongs($queueRow);
            
            // Reset the song counter
            $this->resetSongCounter($station);
            
            // Record the ad play (increment play_count)
            $this->adRepo->recordAdPlay($ad);
        }
    }

    /**
     * Set a cache flag for the frontend to detect a video ad is playing.
     */
    private function setVideoAdCache(Station $station, Advertisement $ad): void
    {
        $cacheKey = self::VIDEO_AD_CACHE_PREFIX . $station->id;
        // Minimum 30 seconds for video ads (YouTube videos need time to load and play)
        $effectiveDuration = (int) $ad->duration;
        if ($effectiveDuration <= 0) {
            $effectiveDuration = 30; // Default 30 seconds for video ads with no duration set
        }
        $cacheTtl = $effectiveDuration + 10; // Cache lives a bit longer than the ad
        
        $adData = [
            'is_ad_playing' => true,
            'ad' => [
                'id' => $ad->id,
                'name' => $ad->name,
                'advertiser_name' => $ad->advertiser_name,
                'media_type' => $ad->media_type->value,
                'media_url' => $ad->media_url,
                'media_path' => $ad->media_path,
                'duration' => $effectiveDuration,
            ],
        ];
        
        $this->cache->set($cacheKey, $adData, $cacheTtl);
        
        $this->logger->info('Video ad cache set for frontend overlay.', [
            'station_id' => $station->id,
            'ad_id' => $ad->id,
            'effective_duration' => $effectiveDuration,
            'cache_ttl' => $cacheTtl,
        ]);
    }

    /**
     * Create a StationQueue entry from an Advertisement (audio ads only).
     */
    private function createQueueFromAd(Station $station, Advertisement $ad): ?StationQueue
    {
        // Create a Song entity for the ad
        $song = Song::createFromText('AD: ' . $ad->name);
        
        $sq = new StationQueue($station, $song);
        $sq->duration = $ad->duration > 0 ? $ad->duration : 30.0;
        $sq->is_visible = false; // Don't show ads in "Playing Next"
        
        // Set the custom URI based on media type
        if (!empty($ad->media_path)) {
            $sq->autodj_custom_uri = $ad->media_path;
        } elseif (!empty($ad->media_url)) {
            $sq->autodj_custom_uri = $ad->media_url;
        } else {
            // No media available for this ad
            $this->logger->warning('Audio ad has no media path or URL.', [
                'ad_id' => $ad->id,
                'ad_name' => $ad->name,
            ]);
            return null;
        }
        
        return $sq;
    }

    /**
     * Get the number of songs played since the last ad for a station.
     */
    private function getSongCounter(Station $station): int
    {
        $key = self::CACHE_PREFIX . $station->id;
        $counter = $this->cache->get($key, 0);
        return (int) $counter;
    }

    /**
     * Increment the song counter for a station.
     */
    private function incrementSongCounter(Station $station): void
    {
        $key = self::CACHE_PREFIX . $station->id;
        $current = $this->getSongCounter($station);
        $this->cache->set($key, $current + 1, 86400); // 24h TTL
    }

    /**
     * Reset the song counter after playing an ad.
     */
    private function resetSongCounter(Station $station): void
    {
        $key = self::CACHE_PREFIX . $station->id;
        $this->cache->set($key, 0, 86400);
    }
}
