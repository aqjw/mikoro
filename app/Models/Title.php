<?php

namespace App\Models;

use App\Casts\RatingCast;
use App\Enums\TitleKind;
use App\Enums\TitleState;
use App\Enums\TitleStatus;
use App\Enums\TitleType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Laravel\Scout\Attributes\SearchUsingFullText;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Title extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;
    use Searchable;

    protected $fillable = [
        'slug',
        'type',
        'kind',
        'title',
        'title_orig',
        'other_title',
        'description',
        'duration',
        'status',
        'state',
        'minimal_age',
        'year',
        'released_at',
        'shikimori_id',
        'shikimori_rating',
        'shikimori_votes',
        'rating',
        'group_id',
        'group_sort',
        'blocked_countries',
        'blocked_seasons',
        'last_episode',
        'episodes_count',
        'last_episode_at',
    ];

    protected $casts = [
        'type' => TitleType::class,
        'kind' => TitleKind::class,
        'status' => TitleStatus::class,
        'state' => TitleState::class,
        'blocked_countries' => 'array',
        'blocked_seasons' => 'array',
        'shikimori_rating' => RatingCast::class,
        'rating' => RatingCast::class,
        'released_at' => 'date',
        'last_episode_at' => 'datetime',
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
    }

    public function studios(): BelongsToMany
    {
        return $this->belongsToMany(Studio::class);
    }

    public function countries(): BelongsToMany
    {
        return $this->belongsToMany(Country::class);
    }

    public function translations(): BelongsToMany
    {
        return $this->belongsToMany(Translation::class);
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
        return $this
            ->hasMany(Title::class, 'group_id', 'group_id')
            ->orderByDesc('group_sort');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(TitleRating::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(TitleBookmark::class);
    }

    public function episodeReleaseSubscriptions(): HasMany
    {
        return $this->hasMany(EpisodeReleaseSubscription::class);
    }

    public function playbackStates(): HasMany
    {
        return $this->hasMany(PlaybackState::class);
    }

    public function singleEpisode(): Attribute
    {
        return Attribute::make(
            fn () => $this->type->is(TitleType::Anime)
        );
    }

    public function userVoted(): Attribute
    {
        return Attribute::make(function () {
            if (! auth()->check()) {
                return false;
            }

            return $this
                ->ratings()
                ->where('user_id', auth()->id())
                ->exists();
        });
    }

    public function bookmarkType(): Attribute
    {
        return Attribute::make(function () {
            if (! auth()->check()) {
                return false;
            }

            return $this
                ->bookmarks()
                ->where('user_id', auth()->id())
                ->first()
                ->type ?? null;
        });
    }

    public function EpisodeReleaseSubscriptionTranslationIds(): Attribute
    {
        return Attribute::make(function () {
            if (! auth()->check()) {
                return [];
            }

            return $this
                ->episodeReleaseSubscriptions()
                ->where('user_id', auth()->id())
                ->pluck('translation_id')
                ->toArray();
        });
    }

    public function purge(): void
    {
        $this->episodes->each->purge();
        $this->comments->each->purge();

        $this->episodeReleaseSubscriptions()->delete();
        $this->bookmarks()->delete();
        $this->ratings()->delete();
        $this->playbackStates()->delete();
        $this->media()->delete();
        $this->delete();
    }
}
