<?php

namespace App\Enums;

use App\Support\ExtendsEnum;

enum CommentReaction: int
{
    use ExtendsEnum;

    case Like = 1;
    case Dislike = 2;

    public static function mapped(): array
    {
        return [
            'like' => self::Like,
            'dislike' => self::Dislike,
        ];
    }
}
