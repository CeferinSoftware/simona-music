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
 * Flow: Canción A → Anuncio → Canción B → (play_frequency more songs) → Anuncio → ...
 *
 * Audio ads: Injected directly into the Liquidsoap queue as the next "song".
 *            The ad plays fully, then the normal AutoDJ resumes.
 * Video ads: A cache flag is set so the frontend overlay displays the video
 *            while the audio stream is muted by the frontend.
 *            A cooldown prevents another ad from triggering while one is active.
 */
final class AdQueueBuilder implements EventSubscriberInterface
{
    use LoggerAwareTrait;
    use EntityManagerAwareTrait;

    private const string CACHE_PREFIX = 'ad_song_counter_';
    private const string VIDEO_AD_CACHE_PREFIX = 'station_current_ad_';
    private const string AD_COOLDOWN_PREFIX = 'ad_cooldown_';

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

        // If an ad is currently in cooldown (recently played), still count the song
        // but don't insert an ad yet.
        if ($this->isOnCooldown($station)) {
            $this->logger->debug('Ad on cooldown, incrementing counter only.', [
                'station_id' => $station->id,
            ]);
            $this->incrementSongCounter($station);
            return;
        }
        
        // Get the best ad to play for this station
        $ad = $this->adRepo->getBestAdForStation($station);
        
        if (null === $ad) {
            $this->logger->debug('No playable ads found for station.', [
                'station_id' => $station->id,
            ]);
            return;
        }

        // Get the song counter for this station
        $songCounter = $this->getSongCounter($station);

        // Check if we've played enough songs to trigger an ad.
        // play_frequency = N means: play an ad after every N songs.
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

        // Calculate effective duration for cooldown
        $effectiveDuration = (int) $ad->duration;
        if ($effectiveDuration <= 0) {
            if ($ad->media_type === AdMediaType::Video) {
                // Video ads with duration=0: "play until video ends naturally".
                // Use a very long cache/cooldown (1 hour) so the frontend controls the end.
                $effectiveDuration = 3600;
            } else {
                $effectiveDuration = 30;
            }
        }

        if ($ad->media_type === AdMediaType::Video) {
            // Video ads: Set cache flag for frontend overlay
            $this->setVideoAdCache($station, $ad, $effectiveDuration);
        } else {
            // Audio ads: Inject into Liquidsoap queue
            $queueRow = $this->createQueueFromAd($station, $ad, $effectiveDuration);
            
            if (null === $queueRow) {
                return; // No media available
            }

            // Set the audio ad info in cache too, so frontend can display overlay
            $this->setAudioAdCache($station, $ad, $effectiveDuration);

            $event->setNextSongs($queueRow);
        }
        
        // Reset the song counter
        $this->resetSongCounter($station);
        
        // Set cooldown to prevent another ad from firing while this one plays.
        // Cooldown = ad duration + small buffer
        $this->setCooldown($station, $effectiveDuration + 15);
        
        // Record the ad play (increment play_count)
        $this->adRepo->recordAdPlay($ad);
    }

    /**
     * Set a cache flag for the frontend to detect a video ad is playing.
     */
    private function setVideoAdCache(Station $station, Advertisement $ad, int $effectiveDuration): void
    {
        $cacheKey = self::VIDEO_AD_CACHE_PREFIX . $station->id;
        // Cache TTL: ad duration + generous buffer so frontend has time to detect and play
        $cacheTtl = $effectiveDuration + 120;
        
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
     * Set a cache flag for the frontend to detect an audio ad is playing.
     */
    private function setAudioAdCache(Station $station, Advertisement $ad, int $effectiveDuration): void
    {
        $cacheKey = self::VIDEO_AD_CACHE_PREFIX . $station->id; // Same key, same endpoint
        // Cache TTL: ad duration + generous buffer
        $cacheTtl = $effectiveDuration + 120;
        
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
        
        $this->logger->info('Audio ad cache set for frontend overlay.', [
            'station_id' => $station->id,
            'ad_id' => $ad->id,
            'effective_duration' => $effectiveDuration,
            'cache_ttl' => $cacheTtl,
        ]);
    }

    /**
     * Create a StationQueue entry from an Advertisement (audio ads only).
     */
    private function createQueueFromAd(Station $station, Advertisement $ad, int $effectiveDuration): ?StationQueue
    {
        // Create a Song entity for the ad
        $song = Song::createFromText('AD: ' . $ad->name);
        
        $sq = new StationQueue($station, $song);
        $sq->duration = (float) $effectiveDuration;
        $sq->is_visible = false; // Don't show ads in "Playing Next"
        
        // Set the custom URI based on media type
        if (!empty($ad->media_url)) {
            $sq->autodj_custom_uri = $ad->media_url;
        } elseif (!empty($ad->media_path)) {
            $sq->autodj_custom_uri = $ad->media_path;
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
     * Check if the station is in ad cooldown (an ad was recently played/scheduled).
     */
    private function isOnCooldown(Station $station): bool
    {
        $key = self::AD_COOLDOWN_PREFIX . $station->id;
        return (bool) $this->cache->get($key, false);
    }

    /**
     * Set a cooldown period after playing an ad.
     */
    private function setCooldown(Station $station, int $seconds): void
    {
        $key = self::AD_COOLDOWN_PREFIX . $station->id;
        $this->cache->set($key, true, $seconds);
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
