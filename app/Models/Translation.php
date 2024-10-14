<?php

namespace App\Models;

use App\Enums\TranslationType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Translation extends Model
{
    use HasFactory;

    protected $fillable = [
        'id', // non-autoIncrement
        'slug',
        'title',
        'type',
    ];

    protected $casts = [
        'type' => TranslationType::class,
    ];

    public function titles(): BelongsToMany
    {
        return $this->belongsToMany(Title::class);
    }
}
