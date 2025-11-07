<?php

declare(strict_types=1);

namespace App\Entity\Repository;

use App\Doctrine\Repository;
use App\Entity\Station;
use App\Entity\StationScreen;

/**
 * @extends Repository<StationScreen>
 */
final class StationScreenRepository extends Repository
{
    /**
     * @return StationScreen[]
     */
    public function fetchForStation(Station $station): array
    {
        return $this->repository->createQueryBuilder('s')
            ->where('s.station = :station')
            ->setParameter('station', $station)
            ->orderBy('s.name', 'ASC')
            ->getQuery()
            ->getResult();
    }
}
