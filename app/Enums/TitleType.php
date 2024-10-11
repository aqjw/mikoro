<?php

namespace App\Enums;

use App\Support\ExtendsEnum;

enum TitleType: int
{
    use ExtendsEnum;

    case Anime = 1;
    case AnimeSerial = 2;

    public static function mapped(): array
    {
        return [
            'anime' => self::Anime,
            'anime-serial' => self::AnimeSerial,
        ];
    }
}
