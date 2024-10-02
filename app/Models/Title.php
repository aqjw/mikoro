<?php

namespace App\Models;

use App\Casts\ShikimoriRatingCast;
use App\Enums\TitleStatus;
use App\Enums\TitleType;
use App\Models\Scopes\BestMatchScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Attributes\SearchUsingPrefix;
use Laravel\Scout\Searchable;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Title extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    use Searchable;

    protected $fillable = [
        'slug',
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

    #[SearchUsingFullText(['description'])]
    public function toSearchableArray(): array
    {
        return [
            'id' => (string) $this->id,
            'slug' => (string) $this->slug,
            'title' => Str::slug($this->title, separator: ' '),
            'title_orig' => Str::slug($this->title_orig, separator: ' '),
            'other_title' => Str::slug($this->other_title, separator: ' '),
            'description' => Str::slug($this->description, separator: ' '),
        ];
    }

    public function searchableAs(): string
    {
        return 'titles_index';
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('poster')
            ->singleFile()
            ->registerMediaConversions(function () {
                $this->addMediaConversion('placeholder')
                    ->format('jpeg')
                    ->quality(40)
                    ->width(100)
                    ->blur(4)
                    ->optimize();
            });

        $this->addMediaCollection('screenshots')
            ->onlyKeepLatest(10)
            ->registerMediaConversions(function () {
                $this->addMediaConversion('placeholder')
                    ->format('jpeg')
                    ->quality(40)
                    ->width(100)
                    ->blur(4)
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
