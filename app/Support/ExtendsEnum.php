<?php

namespace App\Support;

trait ExtendsEnum
{
    public static function mapped(): array
    {
        return [];
    }

    public static function fromName(string $name): ?self
    {
        return self::mapped()[$name] ?? null;
    }

    public function getName(): ?string
    {
        foreach (self::mapped() as $name => $enum) {
            if ($enum === $this) {
                return $name;
            }
        }

        return null;
    }

    public static function getCases(): array
    {
        return array_map(
            fn ($item) => [
                'id' => $item->value,
                'name' => $item->getName(),
            ],
            self::cases()
        );
    }
}
