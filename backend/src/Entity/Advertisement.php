<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enums\AdCategories;
use App\Entity\Enums\AdMediaType;
use App\Entity\Enums\AdStatus;
use App\Entity\Interfaces\EntityGroupsInterface;
use App\Entity\Interfaces\IdentifiableEntityInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    ORM\Index(name: 'idx_ad_status', columns: ['status']),
    ORM\Index(name: 'idx_ad_category', columns: ['category']),
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

    /** @var Collection<int, AdvertisementCategory> */
    #[ORM\OneToMany(targetEntity: AdvertisementCategory::class, mappedBy: 'advertisement', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public Collection $categories;

    /** @var Collection<int, AdvertisementLocation> */
    #[ORM\OneToMany(targetEntity: AdvertisementLocation::class, mappedBy: 'advertisement', cascade: ['persist', 'remove'], orphanRemoval: true)]
    public Collection $locations;

    /** @var Collection<int, AdvertisementPlayLog> */
    #[ORM\OneToMany(targetEntity: AdvertisementPlayLog::class, mappedBy: 'advertisement', cascade: ['remove'])]
    public Collection $play_logs;

    public function __construct()
    {
        $this->created_at = new \DateTime();
        $this->updated_at = new \DateTime();
        $this->categories = new ArrayCollection();
        $this->locations = new ArrayCollection();
        $this->play_logs = new ArrayCollection();
        $this->active_days = [1, 2, 3, 4, 5, 6, 7]; // Todos los días por defecto
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated_at = new \DateTime();
    }

    public function addCategory(AdCategories $category): void
    {
        foreach ($this->categories as $existingCategory) {
            if ($existingCategory->category === $category) {
                return;
            }
        }
        
        $adCategory = new AdvertisementCategory($this, $category);
        $this->categories->add($adCategory);
    }

    public function removeCategory(AdCategories $category): void
    {
        foreach ($this->categories as $key => $existingCategory) {
            if ($existingCategory->category === $category) {
                $this->categories->removeElement($existingCategory);
                return;
            }
        }
    }

    /**
     * @return AdCategories[]
     */
    public function getCategoryValues(): array
    {
        $values = [];
        foreach ($this->categories as $category) {
            $values[] = $category->category;
        }
        return $values;
    }

    public function addLocation(string $province, ?string $city = null, ?string $sector = null): void
    {
        $location = new AdvertisementLocation($this, $province, $city, $sector);
        $this->locations->add($location);
    }

    public function clearLocations(): void
    {
        $this->locations->clear();
    }

    public function clearCategories(): void
    {
        $this->categories->clear();
    }

    /**
     * Verifica si el anuncio está activo para una ubicación y categoría específicas.
     */
    public function isActiveFor(?string $province = null, ?string $city = null, ?string $sector = null, ?AdCategories $category = null): bool
    {
        if ($this->status !== AdStatus::Active) {
            return false;
        }

        $now = new \DateTime();
        
        // Verificar fechas
        if ($this->start_date !== null && $now < $this->start_date) {
            return false;
        }
        if ($this->end_date !== null && $now > $this->end_date) {
            return false;
        }

        // Verificar max plays
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
            $currentDay = (int)$now->format('N');
            if (!in_array($currentDay, $this->active_days, true)) {
                return false;
            }
        }

        // Verificar categoría si se especificó
        if ($category !== null && !$this->categories->isEmpty()) {
            $matchesCategory = false;
            foreach ($this->categories as $adCategory) {
                if ($adCategory->category === $category) {
                    $matchesCategory = true;
                    break;
                }
            }
            if (!$matchesCategory) {
                return false;
            }
        }

        // Verificar ubicación si se especificó
        if ($province !== null && !$this->locations->isEmpty()) {
            $matchesLocation = false;
            foreach ($this->locations as $location) {
                if ($location->matchesLocation($province, $city, $sector)) {
                    $matchesLocation = true;
                    break;
                }
            }
            if (!$matchesLocation) {
                return false;
            }
        }

        return true;
    }

    public function incrementPlayCount(): void
    {
        $this->play_count++;
    }

    public function __toString(): string
    {
        return $this->name ?: 'Nuevo Anuncio';
    }
}
