<?php

namespace App\Services;

use App\Models\User;

class EpisodeReleaseNotificationService
{
    public function subscribe(User $user, int $titleId, array $translationIds): void
    {
        $existingTranslationIds = $user
            ->episodeReleaseNotifications()
            ->where('title_id', $titleId)
            ->pluck('translation_id')
            ->toArray();

        $idsToRemove = array_diff($existingTranslationIds, $translationIds);
        $idsToAdd = array_diff($translationIds, $existingTranslationIds);

        if (filled($idsToRemove)) {
            $user->episodeReleaseNotifications()
                ->where('title_id', $titleId)
                ->whereIn('translation_id', $idsToRemove)
                ->delete();
        }

        if (filled($idsToAdd)) {
            $notifications = array_map(fn ($translationId) => [
                'user_id' => $user->id,
                'title_id' => $titleId,
                'translation_id' => $translationId,
                'created_at' => now(),
                'updated_at' => now(),
            ], $idsToAdd);

            $user->episodeReleaseNotifications()->insert($notifications);
        }
    }
}
