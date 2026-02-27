<?php

declare(strict_types=1);

namespace App\Controller\Api\Stations;

use App\Container\EntityManagerAwareTrait;
use App\Entity\Advertisement;
use App\Entity\Repository\AdvertisementRepository;
use App\Entity\Repository\StationQueueRepository;
use App\Entity\Station;
use App\Http\Response;
use App\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Public API endpoint to get the current advertisement for a station.
 * Used by the frontend player to display video ads and ad badges.
 * 
 * Two sources of ad data:
 * 1. Video ads: Cache flag set by AdQueueBuilder (doesn't interrupt audio stream)
 * 2. Audio ads: Detected from the queue (entries with "AD: " prefix title)
 */
final class AdvertisementAction
{
    use EntityManagerAwareTrait;

    private const string CACHE_PREFIX = 'station_current_ad_';

    public function __construct(
        private readonly StationQueueRepository $queueRepo,
        private readonly AdvertisementRepository $adRepo,
        private readonly CacheInterface $cache,
    ) {
    }

    public function __invoke(
        ServerRequest $request,
        Response $response,
        array $params
    ): ResponseInterface {
        /** @var Station $station */
        $station = $request->getStation();

        $cacheKey = self::CACHE_PREFIX . $station->id;

        // Check the video ad cache first (set by AdQueueBuilder for video ads)
        $cachedAd = $this->cache->get($cacheKey);
        if ($cachedAd !== null && is_array($cachedAd) && ($cachedAd['is_ad_playing'] ?? false)) {
            return $response->withJson($cachedAd);
        }

        // Check queue for audio ads (entries with "AD: " prefix)
        $currentQueue = $this->queueRepo->getUnplayedQueue($station);
        
        $adData = [
            'is_ad_playing' => false,
            'ad' => null,
        ];

        foreach ($currentQueue as $queueRow) {
            $title = $queueRow->title ?? '';
            if (str_starts_with($title, 'AD: ') && !empty($queueRow->autodj_custom_uri)) {
                $adName = substr($title, 4);
                
                // Find the ad entity to get full metadata
                $ads = $this->em->getRepository(Advertisement::class)
                    ->findBy(['name' => $adName, 'status' => 'active']);
                
                $ad = $ads[0] ?? null;
                
                if ($ad !== null) {
                    // Enforce minimum duration (30s for video, 10s for audio)
                    $effectiveDuration = (int) $ad->duration;
                    if ($effectiveDuration <= 0) {
                        $effectiveDuration = $ad->media_type->value === 'video' ? 30 : 10;
                    }
                    
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
                    
                    // Cache for the duration of the ad + buffer
                    $cacheTtl = $effectiveDuration + 10;
                    $this->cache->set($cacheKey, $adData, $cacheTtl);
                }
                
                break;
            }
        }

        return $response->withJson($adData);
    }
}
