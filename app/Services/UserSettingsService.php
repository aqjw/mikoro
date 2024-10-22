<?php

namespace App\Services;

class UserSettingsService
{
    public function getDefault(): array
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
}
