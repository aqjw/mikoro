<?php

namespace App\Models;

use App\Jobs\ProcessEpisodeReleaseSubscriptionJob;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * @property int $id
 * @property Title $title
 */
class Episode extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    protected $fillable = [
        'name',
        'source',
        'translation_id',
    ];

    protected static function booted()
    {
        static::created(function (Episode $episode) {
            $episode->title->touch('last_episode_at');
            ProcessEpisodeReleaseSubscriptionJob::dispatch($episode->id);
        });
    }

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

    public function title(): BelongsTo
    {
        return $this->belongsTo(Title::class);
    }

    public function translation(): BelongsTo
    {
        return $this->belongsTo(Translation::class);
    }
}
