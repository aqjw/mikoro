<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Episode extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'source',
        'translation_id',
    ];

    public function translation(): BelongsTo
    {
        return $this->belongsTo(Translation::class);
    }
}
