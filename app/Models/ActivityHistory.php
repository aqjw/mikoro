<?php

namespace App\Models;

use App\Enums\ActivityHistoryType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityHistory extends Model
{
    protected $fillable = [
        'type',
        'user_id',
        'title_id',
        'context',
    ];

    protected $casts = [
        'type' => ActivityHistoryType::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function title(): BelongsTo
    {
        return $this->belongsTo(Title::class);
    }
}
