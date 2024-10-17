<?php

namespace App\Models;

use App\Enums\BookmarkType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TitleBookmark extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title_id',
        'type',
    ];

    protected $casts = [
        'type' => BookmarkType::class,
    ];

    public function title(): BelongsTo
    {
        return $this->belongsTo(Title::class);
    }
}
