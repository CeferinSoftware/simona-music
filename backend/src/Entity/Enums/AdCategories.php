<?php

declare(strict_types=1);

namespace App\Entity\Enums;

/**
 * Categorías musicales para segmentación de anuncios.
 * Sincronizadas con las categorías de estaciones.
 */
enum AdCategories: string
{
    case Rock = 'rock';
    case Pop = 'pop';
    case Electronic = 'electronic';
    case HipHop = 'hiphop';
    case Reggaeton = 'reggaeton';
    case Bachata = 'bachata';
    case Salsa = 'salsa';
    case Jazz = 'jazz';
    case Classical = 'classical';
    case Country = 'country';
    case RnB = 'rnb';
    case Latin = 'latin';
    case Indie = 'indie';
    case Metal = 'metal';
    case Folk = 'folk';
    case Blues = 'blues';
    case Reggae = 'reggae';
    case Soul = 'soul';
    case Funk = 'funk';
    case Disco = 'disco';
    case House = 'house';
    case Techno = 'techno';
    case Trance = 'trance';
    case DrumAndBass = 'drum_and_bass';
    case Ambient = 'ambient';
    case Lounge = 'lounge';
    case Chillout = 'chillout';
    case World = 'world';
    case Flamenco = 'flamenco';
    case Other = 'other';

    public static function getOptions(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = self::getLabel($case);
        }
        return $options;
    }

    public static function getLabel(self $category): string
    {
        return match ($category) {
            self::Rock => 'Rock',
            self::Pop => 'Pop',
            self::Electronic => 'Electrónica',
            self::HipHop => 'Hip Hop',
            self::Reggaeton => 'Reggaeton',
            self::Bachata => 'Bachata',
            self::Salsa => 'Salsa',
            self::Jazz => 'Jazz',
            self::Classical => 'Clásica',
            self::Country => 'Country',
            self::RnB => 'R&B',
            self::Latin => 'Latina',
            self::Indie => 'Indie',
            self::Metal => 'Metal',
            self::Folk => 'Folk',
            self::Blues => 'Blues',
            self::Reggae => 'Reggae',
            self::Soul => 'Soul',
            self::Funk => 'Funk',
            self::Disco => 'Disco',
            self::House => 'House',
            self::Techno => 'Techno',
            self::Trance => 'Trance',
            self::DrumAndBass => 'Drum & Bass',
            self::Ambient => 'Ambient',
            self::Lounge => 'Lounge',
            self::Chillout => 'Chillout',
            self::World => 'World Music',
            self::Flamenco => 'Flamenco',
            self::Other => 'Otros',
        };
    }
}
