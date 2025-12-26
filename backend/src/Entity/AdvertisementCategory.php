<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Enums\AdCategories;
use App\Entity\Interfaces\IdentifiableEntityInterface;
use Doctrine\ORM\Mapping as ORM;
use OpenApi\Attributes as OA;
use Symfony\Component\Serializer\Annotation as Serializer;

/**
 * Relación entre anuncio y categoría musical.
 */
#[
    OA\Schema(schema: "AdvertisementCategory", type: "object"),
    ORM\Entity,
    ORM\Table(name: 'advertisement_categories'),
    ORM\UniqueConstraint(name: 'unique_ad_category', columns: ['advertisement_id', 'category'])
]
class AdvertisementCategory implements IdentifiableEntityInterface
{
    use Traits\HasAutoIncrementId;

    #[
        ORM\ManyToOne(targetEntity: Advertisement::class, inversedBy: 'categories'),
        ORM\JoinColumn(name: 'advertisement_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')
    ]
    public Advertisement $advertisement;

    #[
        OA\Property(description: "Categoría musical"),
        ORM\Column(type: 'string', length: 50, enumType: AdCategories::class),
        Serializer\Groups(['general'])
    ]
    public AdCategories $category;

    public function __construct(Advertisement $advertisement, AdCategories $category)
    {
        $this->advertisement = $advertisement;
        $this->category = $category;
    }
}
