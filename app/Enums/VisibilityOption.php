<?php

namespace App\Enums;

use App\Support\ExtendsEnum;

enum VisibilityOption: int
{
    use ExtendsEnum;

    case Everyone = 1;
    case AuthenticatedUsers = 2;
    case NoOne = 3;

    public static function mapped(): array
    {
        return [
            'everyone' => self::Everyone,
            'authenticated_users' => self::AuthenticatedUsers,
            'no_one' => self::NoOne,
        ];
    }
}
