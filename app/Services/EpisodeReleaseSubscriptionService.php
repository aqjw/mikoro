<?php

namespace App\Services;

use App\Models\User;

class EpisodeReleaseSubscriptionService
{
    public function subscribe(User $user, int $titleId, array $translationIds): void
    {
        $existingTranslationIds = $user
            ->episodeReleaseSubscriptions()
            ->where('title_id', $titleId)
            ->pluck('translation_id')
            ->toArray();

        $idsToRemove = array_diff($existingTranslationIds, $translationIds);
        $idsToAdd = array_diff($translationIds, $existingTranslationIds);

        if (filled($idsToRemove)) {
            $user->episodeReleaseSubscriptions()
                ->where('title_id', $titleId)
                ->whereIn('translation_id', $idsToRemove)
                ->delete();
        }

        if (filled($idsToAdd)) {
            $subscriptions = array_map(fn ($translationId) => [
                'user_id' => $user->id,
                'title_id' => $titleId,
                'translation_id' => $translationId,
                'created_at' => now(),
                'updated_at' => now(),
            ], $idsToAdd);

            $user->episodeReleaseSubscriptions()->insert($subscriptions);
        }
    }
}
