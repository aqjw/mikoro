<?php

namespace App\Models;

use App\Services\UserService;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class User extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use HasFactory, InteractsWithMedia, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'slug',
        'settings',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'settings' => 'array',
        ];
    }

    protected static function booted()
    {
        static::creating(function (self $user) {
            $service = app(UserService::class);
            $user->settings = $service->getDefaultSettings();
            $user->slug = $service->getSlug($user->email);
        });
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('avatar')
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

    public function playbackStates(): HasMany
    {
        return $this->hasMany(PlaybackState::class);
    }

    public function episodeReleaseSubscriptions(): HasMany
    {
        return $this->hasMany(EpisodeReleaseSubscription::class);
    }

    public function bookmarks(): HasMany
    {
        return $this->hasMany(TitleBookmark::class);
    }

    public function activityHistories(): HasMany
    {
        return $this->hasMany(ActivityHistory::class);
    }
}
