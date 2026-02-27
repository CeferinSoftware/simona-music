<?php

declare(strict_types=1);

namespace App\Entity\Repository;

use App\Container\EntityManagerAwareTrait;
use App\Entity\Advertisement;
use App\Entity\Enums\AdStatus;
use App\Entity\Station;

final class AdvertisementRepository
{
    use EntityManagerAwareTrait;

    /**
     * Get all playable advertisements that match a given station.
     *
     * @return Advertisement[]
     */
    public function getPlayableAdsForStation(Station $station): array
    {
        $allAds = $this->em->createQueryBuilder()
            ->select('a')
            ->from(Advertisement::class, 'a')
            ->where('a.status = :status')
            ->setParameter('status', AdStatus::Active->value)
            ->orderBy('a.priority', 'DESC')
            ->getQuery()
            ->getResult();

        $matchingAds = [];
        foreach ($allAds as $ad) {
            /** @var Advertisement $ad */
            if ($ad->isPlayable() && $ad->matchesStation($station)) {
                $matchingAds[] = $ad;
            }
        }

        return $matchingAds;
    }

    /**
     * Get the best ad to play for a station (highest priority first).
     */
    public function getBestAdForStation(Station $station): ?Advertisement
    {
        $ads = $this->getPlayableAdsForStation($station);
        
        if (empty($ads)) {
            return null;
        }

        // Sort by priority (highest first), then by play_count (lowest first = least played)
        usort($ads, function (Advertisement $a, Advertisement $b) {
            if ($a->priority !== $b->priority) {
                return $b->priority <=> $a->priority;
            }
            return $a->play_count <=> $b->play_count;
        });

        return $ads[0];
    }

    /**
     * Get the minimum play_frequency across all active ads for a station.
     * Returns null if no ads are available.
     */
    public function getMinPlayFrequencyForStation(Station $station): ?int
    {
        $ads = $this->getPlayableAdsForStation($station);
        
        if (empty($ads)) {
            return null;
        }

        $minFrequency = PHP_INT_MAX;
        foreach ($ads as $ad) {
            if ($ad->play_frequency < $minFrequency) {
                $minFrequency = $ad->play_frequency;
            }
        }

        return $minFrequency;
    }

    /**
     * Increment play count and flush.
     */
    public function recordAdPlay(Advertisement $ad): void
    {
        $ad->incrementPlayCount();
        $this->em->persist($ad);
        $this->em->flush();
    }

    /**
     * Find by ID.
     */
    public function find(int $id): ?Advertisement
    {
        return $this->em->find(Advertisement::class, $id);
    }
}
