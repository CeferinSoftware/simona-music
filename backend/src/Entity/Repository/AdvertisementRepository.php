<?php

declare(strict_types=1);

namespace App\Entity\Repository;

use App\Doctrine\ReloadableEntityManagerInterface;
use App\Entity\Advertisement;
use App\Entity\AdvertisementPlayLog;
use App\Entity\Enums\AdCategories;
use App\Entity\Enums\AdStatus;
use App\Entity\Station;
use Doctrine\ORM\EntityRepository;

/**
 * Repositorio para gestión de anuncios publicitarios.
 * @extends EntityRepository<Advertisement>
 */
class AdvertisementRepository extends EntityRepository
{
    public function __construct(
        private readonly ReloadableEntityManagerInterface $em
    ) {
        parent::__construct($em, $em->getClassMetadata(Advertisement::class));
    }

    /**
     * Obtiene todos los anuncios activos.
     * @return Advertisement[]
     */
    public function getActiveAdvertisements(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.status = :status')
            ->setParameter('status', AdStatus::Active)
            ->orderBy('a.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtiene anuncios activos para una estación específica basándose en su categoría y ubicación.
     * @return Advertisement[]
     */
    public function getAdvertisementsForStation(Station $station): array
    {
        $category = $station->ad_category ?? null;
        $province = $station->province ?? null;
        $city = $station->city ?? null;
        $sector = $station->sector ?? null;

        $qb = $this->createQueryBuilder('a')
            ->leftJoin('a.categories', 'ac')
            ->leftJoin('a.locations', 'al')
            ->where('a.status = :status')
            ->setParameter('status', AdStatus::Active)
            ->orderBy('a.priority', 'DESC');

        $ads = $qb->getQuery()->getResult();

        // Filtrar en PHP para lógica compleja de coincidencia
        return array_filter($ads, function (Advertisement $ad) use ($province, $city, $sector, $category) {
            return $ad->isActiveFor($province, $city, $sector, $category);
        });
    }

    /**
     * Obtiene el siguiente anuncio a reproducir para una estación.
     */
    public function getNextAdvertisement(Station $station): ?Advertisement
    {
        $ads = $this->getAdvertisementsForStation($station);
        
        if (empty($ads)) {
            return null;
        }

        // Ordenar por prioridad y menor número de reproducciones
        usort($ads, function (Advertisement $a, Advertisement $b) {
            if ($a->priority !== $b->priority) {
                return $b->priority - $a->priority;
            }
            return $a->play_count - $b->play_count;
        });

        return $ads[0] ?? null;
    }

    /**
     * Registra una reproducción de anuncio.
     */
    public function logPlay(Advertisement $ad, Station $station, int $listenersCount = 0): AdvertisementPlayLog
    {
        $log = new AdvertisementPlayLog($ad, $station);
        $log->listeners_count = $listenersCount;
        $log->actual_duration = $ad->duration;

        $ad->incrementPlayCount();

        $this->em->persist($log);
        $this->em->persist($ad);
        $this->em->flush();

        return $log;
    }

    /**
     * Obtiene estadísticas de reproducciones para un anuncio.
     */
    public function getPlayStats(Advertisement $ad, ?\DateTimeInterface $from = null, ?\DateTimeInterface $to = null): array
    {
        $qb = $this->em->createQueryBuilder()
            ->select('COUNT(pl.id) as total_plays')
            ->addSelect('SUM(pl.listeners_count) as total_listeners')
            ->addSelect('AVG(pl.listeners_count) as avg_listeners')
            ->from(AdvertisementPlayLog::class, 'pl')
            ->where('pl.advertisement = :ad')
            ->setParameter('ad', $ad);

        if ($from !== null) {
            $qb->andWhere('pl.played_at >= :from')
                ->setParameter('from', $from);
        }

        if ($to !== null) {
            $qb->andWhere('pl.played_at <= :to')
                ->setParameter('to', $to);
        }

        return $qb->getQuery()->getSingleResult();
    }

    /**
     * Obtiene anuncios por categoría.
     * @return Advertisement[]
     */
    public function getByCategory(AdCategories $category): array
    {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.categories', 'ac')
            ->where('ac.category = :category')
            ->setParameter('category', $category)
            ->orderBy('a.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Obtiene anuncios por ubicación.
     * @return Advertisement[]
     */
    public function getByLocation(string $province, ?string $city = null): array
    {
        $qb = $this->createQueryBuilder('a')
            ->innerJoin('a.locations', 'al')
            ->where('al.province = :province')
            ->setParameter('province', $province);

        if ($city !== null) {
            $qb->andWhere('al.city = :city OR al.city IS NULL')
                ->setParameter('city', $city);
        }

        return $qb->orderBy('a.priority', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Guarda un anuncio.
     */
    public function save(Advertisement $ad): void
    {
        $this->em->persist($ad);
        $this->em->flush();
    }

    /**
     * Elimina un anuncio.
     */
    public function delete(Advertisement $ad): void
    {
        $this->em->remove($ad);
        $this->em->flush();
    }

    /**
     * Obtiene todas las provincias únicas usadas en anuncios.
     * @return string[]
     */
    public function getUniqueProvinces(): array
    {
        $result = $this->em->createQueryBuilder()
            ->select('DISTINCT al.province')
            ->from(\App\Entity\AdvertisementLocation::class, 'al')
            ->orderBy('al.province', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_column($result, 'province');
    }

    /**
     * Obtiene todas las ciudades únicas para una provincia.
     * @return string[]
     */
    public function getUniqueCities(string $province): array
    {
        $result = $this->em->createQueryBuilder()
            ->select('DISTINCT al.city')
            ->from(\App\Entity\AdvertisementLocation::class, 'al')
            ->where('al.province = :province')
            ->andWhere('al.city IS NOT NULL')
            ->setParameter('province', $province)
            ->orderBy('al.city', 'ASC')
            ->getQuery()
            ->getScalarResult();

        return array_filter(array_column($result, 'city'));
    }
}
