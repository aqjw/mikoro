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

    public function getLabel(): ?string
    {
        return match ($this) {
            self::Anons => 'Anons',
            self::Ongoing => 'Ongoing',
            self::Released => 'Released',
        };
    }

    public static function getCases(): array
    {
        return array_map(
            fn ($item) => [
                'id' => $item->value,
                'name' => $item->getLabel(),
            ],
            self::cases()
        );
    }
}

