<?php

declare(strict_types=1);

namespace App\Entity;

use App\Utilities\Time;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;

#[
    ORM\Entity,
    ORM\Table(name: 'station_screens')
]
final class StationScreen implements
    Interfaces\IdentifiableEntityInterface,
    Interfaces\StationAwareInterface
{
    use Traits\HasAutoIncrementId;
    use Traits\TruncateStrings;

    #[ORM\ManyToOne(inversedBy: 'screens')]
    #[ORM\JoinColumn(name: 'station_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    public readonly Station $station;

    #[ORM\Column(nullable: false, insertable: false, updatable: false)]
    public private(set) int $station_id;

    #[ORM\Column(length: 100)]
    public string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    public ?string $description = null;

    #[ORM\Column]
    public bool $is_active = true;

    #[ORM\Column(length: 50)]
    public string $content_type = 'nowplaying';

    #[ORM\Column(type: 'json', nullable: true)]
    public ?array $metadata = null;

    #[ORM\Column(type: 'datetime_immutable', precision: 6)]
    public readonly DateTimeImmutable $created_at;

    #[ORM\Column(type: 'datetime_immutable', precision: 6)]
    public DateTimeImmutable $updated_at {
        set(DateTimeImmutable|string $value) => Time::toUtcCarbonImmutable($value);
    }

    public function __construct(Station $station, string $name)
    {
        $this->station = $station;
        $this->name = $this->truncateString($name, 100);
        $this->created_at = Time::nowUtc();
        $this->updated_at = Time::nowUtc();
    }

    public function update(): void
    {
        $this->updated_at = Time::nowUtc();
    }

    /**
     * Get the public URL for this screen
     */
    public function getPublicUrl(string $baseUrl): string
    {
        return $baseUrl . '/public/' . $this->station_id . '/screen/' . $this->getIdRequired();
    }
}
