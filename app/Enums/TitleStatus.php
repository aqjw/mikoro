<?php

namespace App\Enums;

enum TitleStatus: int
{
    case Anons = 1;
    case Ongoing = 2;
    case Released = 3;

    public static function fromName(string $name): ?self
    {
        return match ($name) {
            'anons' => self::Anons,
            'ongoing' => self::Ongoing,
            'released' => self::Released,
            default => null,
        };
    }
}

