<?php

namespace App\Services;

use App\Enums\ActivityHistoryType;
use App\Models\ActivityHistory;
use App\Models\Episode;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ActivityHistoryService
{
    public function get(User $user, int $limit = 28): LengthAwarePaginator
    {
        $query = ActivityHistory::query()
            ->join('titles', 'activity_histories.title_id', '=', 'titles.id')
            ->where('activity_histories.user_id', $user->id)
            ->select([
                'activity_histories.id',
                'activity_histories.context',
                'activity_histories.type',
                'activity_histories.created_at',
                'titles.slug as title_slug',
                'titles.title as title_title',
            ])
            ->latest('activity_histories.id');

        $result = $query->paginate($limit);

        return $result;
    }

    public function heatmap(User $user): array
    {
        return ActivityHistory::query()
            ->where('user_id', $user->id)
            ->where('type', ActivityHistoryType::Episode)
            ->select([
                DB::raw('DATE(created_at) as date'),
                DB::raw('count(*) as count'),
            ])
            ->groupBy('date')
            ->get()
            ->toArray();
    }

    public function store(User $user, int $titleId, ActivityHistoryType $type, ?string $context): ActivityHistory
    {
        return $user->activityHistories()->create([
            'type' => $type,
            'title_id' => $titleId,
            'context' => $context,
        ]);
    }

    public function storeEpisodeOrNone(User $user, int $titleId, int $episodeId): ?ActivityHistory
    {
        $episodeName = Episode::find($episodeId)->name;

        $alreadyExists = $user->activityHistories()
            ->where('type', ActivityHistoryType::Episode)
            ->where('title_id', $titleId)
            // TODO: context is string column without index(sql)
            ->where('context', $episodeName)
            ->exists();

        if ($alreadyExists) {
            return null;
        }

        return $this->store(
            user: $user,
            titleId: $titleId,
            type: ActivityHistoryType::Episode,
            context: $episodeName,
        );
    }
}
