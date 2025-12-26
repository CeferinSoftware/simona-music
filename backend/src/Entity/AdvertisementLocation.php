<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interfaces\IdentifiableEntityInterface;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Relación entre anuncio y ubicación geográfica (provincia, ciudad, sector).
 */
#[
    OA\Schema(schema: "AdvertisementLocation", type: "object"),
    ORM\Entity,
    ORM\Table(name: 'advertisement_locations'),
    ORM\Index(name: 'idx_ad_location_province', columns: ['province']),
    ORM\Index(name: 'idx_ad_location_city', columns: ['city'])
]
class AdvertisementLocation implements IdentifiableEntityInterface
{
    use Traits\HasAutoIncrementId;
    use Traits\TruncateStrings;

    #[
        ORM\ManyToOne(targetEntity: Advertisement::class, inversedBy: 'locations'),
        ORM\JoinColumn(name: 'advertisement_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')
    ]
    public Advertisement $advertisement;

    #[
        OA\Property(description: "Provincia", example: "Madrid"),
        ORM\Column(length: 100, nullable: false),
        Serializer\Groups(['general'])
    ]
    public string $province = '';

    #[
        OA\Property(description: "Ciudad", example: "Alcobendas"),
        ORM\Column(length: 100, nullable: true),
        Serializer\Groups(['general'])
    ]
    public ?string $city = null;

    #[
        OA\Property(description: "Sector/Barrio", example: "Centro"),
        ORM\Column(length: 100, nullable: true),
        Serializer\Groups(['general'])
    ]
    public ?string $sector = null;

    public function __construct(
        Advertisement $advertisement,
        string $province,
        ?string $city = null,
        ?string $sector = null
    ) {
        $this->advertisement = $advertisement;
        $this->province = $province;
        $this->city = $city;
        $this->sector = $sector;
    }

    /**
     * Verifica si esta ubicación coincide con los parámetros dados.
     * La lógica es jerárquica: si se especifica solo provincia, coincide con todas las ciudades de esa provincia.
     */
    public function matchesLocation(string $province, ?string $city = null, ?string $sector = null): bool
    {
        // Primero, la provincia debe coincidir
        if (strtolower($this->province) !== strtolower($province)) {
            return false;
        }

        // Si el anuncio tiene ciudad específica pero la estación no proporciona ciudad
        if ($this->city !== null && $city === null) {
            return false;
        }

        // Si ambos tienen ciudad, deben coincidir
        if ($this->city !== null && $city !== null) {
            if (strtolower($this->city) !== strtolower($city)) {
                return false;
            }
        }

        // Si el anuncio tiene sector específico pero la estación no lo proporciona
        if ($this->sector !== null && $sector === null) {
            return false;
        }

        // Si ambos tienen sector, deben coincidir
        if ($this->sector !== null && $sector !== null) {
            if (strtolower($this->sector) !== strtolower($sector)) {
                return false;
            }
        }

        return true;
    }

    public function getFullLocation(): string
    {
        $parts = [$this->province];
        if ($this->city !== null) {
            $parts[] = $this->city;
        }
        if ($this->sector !== null) {
            $parts[] = $this->sector;
        }
        return implode(' > ', $parts);
    }
}
