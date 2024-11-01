<?php

namespace App\Enums;

use App\Support\ExtendsEnum;

enum TranslationType: int
{
    use ExtendsEnum;

    case Subtitles = 1;
    case Voice = 2;

    public static function mapped(): array
    {
        return [
            'subtitles' => self::Subtitles,
            'voice' => self::Voice,
        ];
    }
}
