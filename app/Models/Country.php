<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Country extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'name',
    ];

    public function titles(): BelongsToMany
    {
        return $this->belongsToMany(Title::class);
    }
}
