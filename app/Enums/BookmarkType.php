<?php

namespace App\Enums;

use App\Support\ExtendsEnum;

enum BookmarkType: int
{
    use ExtendsEnum;

    case Planned = 1;
    case Watching = 2;
    case Dropped = 3;
    case Completed = 5;

    public static function mapped(): array
    {
        return [
            'planned' => self::Planned,
            'watching' => self::Watching,
            'dropped' => self::Dropped,
            'completed' => self::Completed,
        ];
    }
}
