<?php

namespace App\Enums;

use App\Support\ExtendsEnum;

enum TitleStatus: int
{
    use ExtendsEnum;

    case Anons = 1;
    case Ongoing = 2;
    case Released = 3;

    public static function mapped(): array
    {
        return [
            'anons' => self::Anons,
            'ongoing' => self::Ongoing,
            'released' => self::Released,
        ];
    }
}
