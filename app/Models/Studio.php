<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Laravel\Scout\Searchable;

class Studio extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'slug',
        'name',
    ];

    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->id,
            'slug' => (string) $this->slug,
            'name' => Str::slug($this->name, separator: ' '),
        ];
    }

    public function searchableAs(): string
    {
        return 'studios_index';
    }
}
