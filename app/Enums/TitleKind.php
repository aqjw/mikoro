<?php

namespace App\Enums;

use App\Support\ExtendsEnum;

enum TitleKind: int
{
    use ExtendsEnum;

    case TV = 1;
    case Movie = 2;
    case OVA = 3;
    case ONA = 4;
    case TV13 = 5;
    case TV24 = 6;
    case TV48 = 7;
    // case Special = 8;
    // case Music = 9;

    public static function mapped(): array
    {
        return [
            'tv' => self::TV,
            'movie' => self::Movie,
            'ova' => self::OVA,
            'ona' => self::ONA,
            'tv_13' => self::TV13,
            'tv_24' => self::TV24,
            'tv_48' => self::TV48,
            // 'special' => self::Special,
            // 'music' => self::Music,
        ];
    }
}
