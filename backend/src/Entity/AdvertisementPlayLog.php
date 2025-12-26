<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Interfaces\IdentifiableEntityInterface;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Registro de reproducciones de anuncios para analytics y facturación.
 */
#[
    OA\Schema(schema: "AdvertisementPlayLog", type: "object"),
    ORM\Entity,
    ORM\Table(name: 'advertisement_play_logs'),
    ORM\Index(name: 'idx_ad_playlog_date', columns: ['played_at']),
    ORM\Index(name: 'idx_ad_playlog_station', columns: ['station_id'])
]
class AdvertisementPlayLog implements IdentifiableEntityInterface
{
    use Traits\HasAutoIncrementId;

    #[
        ORM\ManyToOne(targetEntity: Advertisement::class, inversedBy: 'play_logs'),
        ORM\JoinColumn(name: 'advertisement_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')
    ]
    public Advertisement $advertisement;

    #[
        ORM\ManyToOne(targetEntity: Station::class),
        ORM\JoinColumn(name: 'station_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')
    ]
    public Station $station;

    #[
        OA\Property(description: "Fecha y hora de reproducción"),
        ORM\Column(type: 'datetime', nullable: false),
        Serializer\Groups(['general'])
    ]
    public \DateTimeInterface $played_at;

    #[
        OA\Property(description: "Número de oyentes en el momento de reproducción"),
        ORM\Column(type: 'integer', nullable: false, options: ['default' => 0]),
        Serializer\Groups(['general'])
    ]
    public int $listeners_count = 0;

    #[
        OA\Property(description: "Duración real de reproducción en segundos"),
        ORM\Column(type: 'float', nullable: false, options: ['default' => 0]),
        Serializer\Groups(['general'])
    ]
    public float $actual_duration = 0.0;

    #[
        OA\Property(description: "Si la reproducción fue completa"),
        ORM\Column(type: 'boolean', nullable: false, options: ['default' => true]),
        Serializer\Groups(['general'])
    ]
    public bool $completed = true;

    public function __construct(Advertisement $advertisement, Station $station)
    {
        $this->advertisement = $advertisement;
        $this->station = $station;
        $this->played_at = new \DateTime();
    }
}
