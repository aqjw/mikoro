<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Str;

class UserService
{
    public function getDefaultSettings(): array
    {
        return [
            'notifications' => [
                'comment_reply' => true,
                'site_updates' => true,
                'news_alerts' => true,
            ],
            'player' => [
                'default_quality' => '1080p',
                'default_translation' => null,
            ],
            'privacy' => [
                'list_visibility' => 1,
                'history_visibility' => 1,
            ],
        ];
    }

    public function getSlug(string $email): string
    {
        $originSlug = Str::of($email)->explode('@')->first();
        $slug = $originSlug;

        $counter = 1;

        while (User::where('slug', $slug)->exists()) {
            $slug = "{$originSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
