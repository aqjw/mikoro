<?php

namespace App\Services;

use App\Enums\ActivityHistoryType;
use App\Enums\BookmarkType;
use App\Models\Title;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BookmarkService
{
    public function get(User $user, BookmarkType $bookmarkType, array $sorting, int $limit = 28, bool $withMedia = false): LengthAwarePaginator
    {
        $query = Title::query()
            ->join('title_bookmarks', 'title_bookmarks.title_id', '=', 'titles.id')
            ->where('title_bookmarks.type', $bookmarkType)
            ->where('title_bookmarks.user_id', $user->id)
            ->select([
                'title_bookmarks.updated_at',
                'titles.id',
                'titles.slug',
                'titles.title',
                'titles.released_at',
                'titles.last_episode',
                'titles.shikimori_rating',
                'titles.shikimori_votes',
                'titles.type',
            ]);

        if ($withMedia) {
            $query->with([
                'media' => fn ($query) => $query->where('collection_name', 'poster'),
            ]);
        }

        $this->sorting($query, $sorting);
        $result = $query->paginate($limit);

        return $result;
    }

    public function sorting($query, array $sorting): void
    {
        $order = in_array(
            $sorting['order'] ?? 'desc',
            ['asc', 'desc']
        ) ? $sorting['order'] : 'desc';

        match ($sorting['key']) {
            'title' => $query->orderBy('titles.title', $order),
            'released_at' => $query->orderBy('titles.released_at', $order),
            'rating' => $query->orderBy('titles.shikimori_rating', $order),
            'last_episode' => $query->orderBy('titles.last_episode', $order),
            'updated_at' => $query->orderBy('title_bookmarks.updated_at', $order),
            default => null
        };
    }

    public function toggle(User $user, int $titleId, ?int $type): void
    {
        if (blank($type)) {
            $user->bookmarks()->where('title_id', $titleId)->delete();
        } else {
            $user->bookmarks()->updateOrCreate(
                ['title_id' => $titleId],
                ['type' => $type],
            );

            app(ActivityHistoryService::class)->store(
                user: $user,
                titleId: $titleId,
                type: ActivityHistoryType::Bookmark,
                context: BookmarkType::tryFrom($type)->getName(),
            );
        }
    }

    public function summary(User $user): array
    {
        return $user
            ->bookmarks()
            ->groupBy('type')
            ->select([
                DB::raw('count(*) as count'),
                'type',
            ])
            ->get()
            ->pluck('count', 'type.value')
            ->toArray();
    }
}
