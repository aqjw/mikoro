<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Episode extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'source',
        'translation_id',
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('screenshots')
            ->onlyKeepLatest(5)
            ->registerMediaConversions(function () {
                $this->addMediaConversion('placeholder')
                    ->format('jpeg')
                    ->quality(40)
                    ->width(100)
                    ->blur(4)
                    ->optimize();
            });
    }

    public function translation(): BelongsTo
    {
        return $this->belongsTo(Translation::class);
    }
}
