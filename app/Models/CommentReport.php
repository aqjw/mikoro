<?php

namespace App\Models;

use App\Enums\CommentReportReason;
use Illuminate\Database\Eloquent\Model;

class CommentReport extends Model
{
    protected $fillable = [
        'comment_id',
        'user_id',
        'reason',
    ];

    protected $casts = [
        'reason' => CommentReportReason::class,
    ];
}
