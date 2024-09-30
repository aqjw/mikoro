<?php

namespace App\Models;

use App\Casts\ShikimoriRatingCast;
use App\Enums\TitleStatus;
use App\Enums\TitleType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Title extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'kid',
        'type',
        'title',
        'title_orig',
        'other_title',
        'description',
        'duration',
        'status',
        'minimal_age',
        'year',
        'shikimori_id',
        'shikimori_rating',
        'group_id',
        'blocked_countries',
        'blocked_seasons',
        'last_episode',
        'episodes_count',
    ];

    protected $casts = [
        'type' => TitleType::class,
        'status' => TitleStatus::class,
        'blocked_countries' => 'array',
        'blocked_seasons' => 'array',
        'shikimori_rating' => ShikimoriRatingCast::class,
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('screenshots')
            ->onlyKeepLatest(10);

        $this->addMediaCollection('poster')
            ->singleFile()
            ->registerMediaConversions(function () {
                $this->addMediaConversion('small')
                    ->fit(Fit::Crop, 100, 100)
                    ->optimize();
            });
    }

    public function studios(): BelongsToMany
    {
        return $this->belongsToMany(Studio::class);
    }

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class);
    }

    public function episodes(): HasMany
    {
        return $this->hasMany(Episode::class);
    }

    public function related(): HasMany
    {
        return $this->hasMany(Title::class, 'group_id');
    }
}
