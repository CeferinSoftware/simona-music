<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enums\AdMediaType;
use App\Entity\Enums\AdStatus;
use App\Entity\Interfaces\EntityGroupsInterface;
use App\Entity\Interfaces\IdentifiableEntityInterface;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Entidad principal para gestión de anuncios publicitarios.
 * Los anuncios se segmentan por categoría musical y ubicación geográfica.
 */
#[
    OA\Schema(schema: "Advertisement", type: "object"),
    ORM\Entity,
    ORM\Table(name: 'advertisements'),
    ORM\HasLifecycleCallbacks
]
class Advertisement implements IdentifiableEntityInterface
{
    use Traits\HasAutoIncrementId;
    use Traits\TruncateStrings;

    #[
        OA\Property(description: "Nombre del anuncio", example: "Promo Verano 2024"),
        ORM\Column(length: 255, nullable: false),
        Assert\NotBlank,
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public string $name = '';

    #[
        OA\Property(description: "Descripción del anuncio"),
        ORM\Column(type: 'text', nullable: true),
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public ?string $description = null;

    #[
        OA\Property(description: "Tipo de medio: audio o video"),
        ORM\Column(type: 'string', length: 20, enumType: AdMediaType::class),
        Assert\NotBlank,
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public AdMediaType $media_type = AdMediaType::Audio;

    #[
        OA\Property(description: "Ruta del archivo de media"),
        ORM\Column(length: 500, nullable: true),
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public ?string $media_path = null;

    #[
        OA\Property(description: "URL externa del anuncio (YouTube, etc.)"),
        ORM\Column(length: 500, nullable: true),
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public ?string $media_url = null;

    #[
        OA\Property(description: "Duración del anuncio en segundos"),
        ORM\Column(type: 'float', nullable: false, options: ['default' => 0]),
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public float $duration = 0.0;

    #[
        OA\Property(description: "Estado del anuncio"),
        ORM\Column(type: 'string', length: 20, enumType: AdStatus::class),
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public AdStatus $status = AdStatus::Active;

    #[
        OA\Property(description: "Prioridad del anuncio (mayor = más prioridad)", example: 5),
        ORM\Column(type: 'integer', nullable: false, options: ['default' => 5]),
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public int $priority = 5;

    #[
        OA\Property(description: "Nombre del cliente/anunciante"),
        ORM\Column(length: 255, nullable: true),
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public ?string $advertiser_name = null;

    #[
        OA\Property(description: "Categorías musicales objetivo (JSON array)"),
        ORM\Column(type: 'json', nullable: true),
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public ?array $target_categories = null;

    #[
        OA\Property(description: "Provincias objetivo (JSON array)"),
        ORM\Column(type: 'json', nullable: true),
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public ?array $target_provinces = null;

    #[
        OA\Property(description: "Ciudades objetivo (JSON array)"),
        ORM\Column(type: 'json', nullable: true),
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public ?array $target_cities = null;

    #[
        OA\Property(description: "IDs de estaciones/terrazas objetivo (JSON array)"),
        ORM\Column(type: 'json', nullable: true),
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public ?array $target_stations = null;

    #[
        OA\Property(description: "Fecha de inicio de validez del anuncio"),
        ORM\Column(type: 'datetime', nullable: true),
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public ?\DateTimeInterface $start_date = null;

    #[
        OA\Property(description: "Fecha de fin de validez del anuncio"),
        ORM\Column(type: 'datetime', nullable: true),
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public ?\DateTimeInterface $end_date = null;

    #[
        OA\Property(description: "Número máximo de reproducciones (0 = ilimitado)"),
        ORM\Column(type: 'integer', nullable: false, options: ['default' => 0]),
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public int $max_plays = 0;

    #[
        OA\Property(description: "Número actual de reproducciones"),
        ORM\Column(type: 'integer', nullable: false, options: ['default' => 0]),
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public int $play_count = 0;

    #[
        OA\Property(description: "Frecuencia de reproducción (cada X canciones)"),
        ORM\Column(type: 'integer', nullable: false, options: ['default' => 5]),
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public int $play_frequency = 5;

    #[
        OA\Property(description: "Hora de inicio para reproducir (formato HH:MM)"),
        ORM\Column(length: 5, nullable: true),
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public ?string $time_start = null;

    #[
        OA\Property(description: "Hora de fin para reproducir (formato HH:MM)"),
        ORM\Column(length: 5, nullable: true),
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public ?string $time_end = null;

    #[
        OA\Property(description: "Días de la semana activos (array JSON)"),
        ORM\Column(type: 'json', nullable: true),
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public ?array $active_days = null;

    #[
        ORM\Column(type: 'datetime', nullable: false),
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public \DateTimeInterface $created_at;

    #[
        ORM\Column(type: 'datetime', nullable: false),
        Serializer\Groups([EntityGroupsInterface::GROUP_GENERAL, EntityGroupsInterface::GROUP_ALL])
    ]
    public \DateTimeInterface $updated_at;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
        $this->active_days = [1, 2, 3, 4, 5, 6, 7]; // Todos los días por defecto
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated_at = new \DateTime();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
    }

    /**
     * Verifica si el anuncio está activo y válido para reproducirse.
     */
    public function isPlayable(): bool
    {
        if ($this->status !== AdStatus::Active) {
            return false;
        }

        $now = new \DateTime();

        // Verificar fechas de validez
        if ($this->start_date !== null && $now < $this->start_date) {
            return false;
        }
        if ($this->end_date !== null && $now > $this->end_date) {
            return false;
        }

        // Verificar límite de reproducciones
        if ($this->max_plays > 0 && $this->play_count >= $this->max_plays) {
            return false;
        }

        // Verificar horario
        if ($this->time_start !== null && $this->time_end !== null) {
            $currentTime = $now->format('H:i');
            if ($currentTime < $this->time_start || $currentTime > $this->time_end) {
                return false;
            }
        }

        // Verificar día de la semana
        if ($this->active_days !== null && !empty($this->active_days)) {
            $currentDay = (int)$now->format('N'); // 1 = Monday, 7 = Sunday
            if (!in_array($currentDay, $this->active_days, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Verifica si el anuncio es válido para una estación específica.
     */
    public function matchesStation(Station $station): bool
    {
        // Si se especifican estaciones concretas, verificar primero
        if (!empty($this->target_stations)) {
            return in_array($station->id, $this->target_stations, false);
        }

        // Si no hay restricciones, aplica a todas las estaciones
        $hasRestrictions = !empty($this->target_categories) || 
                           !empty($this->target_provinces) || 
                           !empty($this->target_cities);

        if (!$hasRestrictions) {
            return true;
        }

        // Verificar categoría
        if (!empty($this->target_categories)) {
            $stationCategory = $station->ad_category ?? null;
            if ($stationCategory !== null && !in_array($stationCategory, $this->target_categories, true)) {
                return false;
            }
        }

        // Verificar provincia
        if (!empty($this->target_provinces)) {
            $stationProvince = $station->province ?? null;
            if ($stationProvince !== null && !in_array($stationProvince, $this->target_provinces, true)) {
                return false;
            }
        }

        // Verificar ciudad
        if (!empty($this->target_cities)) {
            $stationCity = $station->city ?? null;
            if ($stationCity !== null && !in_array($stationCity, $this->target_cities, true)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Incrementa el contador de reproducciones.
     */
    public function incrementPlayCount(): void
    {
        $this->play_count++;
    }
}
