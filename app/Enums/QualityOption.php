<?php

namespace App\Enums;

use App\Support\ExtendsEnum;

enum QualityOption: int
{
    use ExtendsEnum;

    case Q360p = 360;
    case Q480p = 480;
    case Q720p = 720;
    case Q1080p = 1080;

    public static function mapped(): array
    {
        return [
            '360p' => self::Q360p,
            '480p' => self::Q480p,
            '720p' => self::Q720p,
            '1080p' => self::Q1080p,
        ];
    }
}
