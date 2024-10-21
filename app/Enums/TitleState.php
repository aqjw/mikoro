<?php

namespace App\Enums;

use App\Support\ExtendsEnum;

enum TitleState: int
{
    use ExtendsEnum;

    case Active = 1;
    case Blocked = 2;

    public static function mapped(): array
    {
        return [
            'active' => self::Active,
            'blocked' => self::Blocked,
        ];
    }
}
