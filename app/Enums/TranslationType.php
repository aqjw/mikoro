<?php

namespace App\Enums;

enum TranslationType: int
{
    case Subtitles = 1;
    case Voice = 2;

    public static function fromName(string $name): ?self
    {
        return match ($name) {
            'subtitles' => self::Subtitles,
            'voice' => self::Voice,
            default => null,
        };
    }
}

