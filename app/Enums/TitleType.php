<?php

namespace App\Enums;

enum TitleType: int
{
    case Anime = 1;
    case AnimeSerial = 2;

    public static function fromName(string $name): ?self
    {
        return match ($name) {
            'anime' => self::Anime,
            'anime-serial' => self::AnimeSerial,
            default => null,
        };
    }
}

