<?php

namespace App\Enums;

use App\Support\ExtendsEnum;

enum BookmarkType: int
{
    use ExtendsEnum;

    case Planned = 1;
    case Watching = 2;
    case Completed = 4;
    case Dropped = 5;

    public static function mapped(): array
    {
        return [
            'planned' => self::Planned,
            'watching' => self::Watching,
            'completed' => self::Completed,
            'dropped' => self::Dropped,
        ];
    }
}
