<?php

declare(strict_types=1);

namespace App\Entity\Enums;

/**
 * Tipo de medio del anuncio (audio o video).
 */
enum AdMediaType: string
{
    case Audio = 'audio';
    case Video = 'video';

    public static function getOptions(): array
    {
        return [
            self::Audio->value => 'Audio',
            self::Video->value => 'Vídeo',
        ];
    }
}
