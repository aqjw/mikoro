<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommentReaction extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'comment_id',
        'user_id',
        'reaction',
    ];

    protected $casts = [
        'reaction' => \App\Enums\CommentReaction::class,
    ];
}
