<?php

declare(strict_types=1);

namespace App\Controller\Api\Stations;

use App\Entity\Station;
use App\Http\Response;
use App\Http\ServerRequest;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Public API endpoint to get the current advertisement for a station.
 * Used by the frontend player to display video/audio ad overlays.
 * 
 * Ad data is set in cache by AdQueueBuilder when an ad is queued.
 * This endpoint simply reads that cache and returns it.
 */
final class AdvertisementAction
{
    private const string CACHE_PREFIX = 'station_current_ad_';

    public function __construct(
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

        // Check the ad cache (set by AdQueueBuilder for both video and audio ads)
        $cachedAd = $this->cache->get($cacheKey);
        if ($cachedAd !== null && is_array($cachedAd) && ($cachedAd['is_ad_playing'] ?? false)) {
            // Don't report ad as playing before the current song ends
            $notBefore = $cachedAd['not_before'] ?? 0;
            if ($notBefore > 0 && time() < $notBefore) {
                return $response->withJson([
                    'is_ad_playing' => false,
                    'ad' => null,
                ]);
            }
            return $response->withJson($cachedAd);
        }

        // No ad playing
        return $response->withJson([
            'is_ad_playing' => false,
            'ad' => null,
        ]);
    }
}
