<?php

namespace App\Enums;

use App\Support\ExtendsEnum;

enum ActivityHistoryType: int
{
    use ExtendsEnum;

    case Episode = 1;
    case Bookmark = 2;

    public static function mapped(): array
    {
        return [
            'episode' => self::Episode,
            'bookmark' => self::Bookmark,
        ];
    }
}
