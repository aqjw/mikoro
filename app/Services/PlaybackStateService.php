<?php

namespace App\Services;

use App\Models\PlaybackState;
use App\Models\Title;
use App\Models\User;

class PlaybackStateService
{
    public function get(User $user, Title $title): PlaybackState
    {
        $playbackState = $user
            ->playbackStates()
            ->where('title_id', $title->id)
            ->first();

        if (! $playbackState) {
            // TODO: not just first. you need to sort by translations..
            $episode = $title->episodes()->first();
            $playbackState = $user->playbackStates()->create([
                'title_id' => $title->id,
                'episode_id' => $episode->id,
                'translation_id' => $episode->translation_id,
                'time' => 0,
            ]);
        }

        return $playbackState;
    }

    public function save(User $user, Title $title, array $data): void
    {
        $user
            ->playbackStates()
            ->updateOrCreate(
                ['title_id' => $title->id],
                [
                    'episode_id' => $data['episode_id'],
                    'translation_id' => $data['translation_id'],
                    'time' => $data['time'],
                ]
            );

        $activityHistoryService = app(ActivityHistoryService::class);
        $activityHistoryService->storeEpisodeOrNone(
            user: $user,
            titleId: $title->id,
            episodeId: $data['episode_id'],
        );
    }
}
