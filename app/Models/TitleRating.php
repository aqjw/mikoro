<?php

namespace App\Models;

use App\Casts\RatingCast;
use App\Services\TitleRatingService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TitleRating extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'title_id',
        'user_id',
        'rating',
    ];

    protected $casts = [
        'rating' => RatingCast::class,
    ];

    protected static function booted()
    {
        static::created(function (self $model) {
            app(TitleRatingService::class)->calculateAverageRating($model);
        });

        static::updated(function (self $model) {
            app(TitleRatingService::class)->calculateAverageRating($model);
        });
    }

    public function title(): BelongsTo
    {
        return $this->belongsTo(Title::class);
    }
}
