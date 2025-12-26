<?php

declare(strict_types=1);

namespace App\Entity\Enums;

/**
 * Estado del anuncio.
 */
enum AdStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Scheduled = 'scheduled';
    case Expired = 'expired';

    public static function getOptions(): array
    {
        return [
            self::Active->value => 'Activo',
            self::Inactive->value => 'Inactivo',
            self::Scheduled->value => 'Programado',
            self::Expired->value => 'Expirado',
        ];
    }
}
