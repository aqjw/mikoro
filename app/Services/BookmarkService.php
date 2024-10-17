<?php

namespace App\Services;

use App\Enums\BookmarkType;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class BookmarkService
{
    public function get(User $user, BookmarkType $bookmarkType, array $sorting, int $limit = 28): LengthAwarePaginator
    {
        $query = $user
            ->bookmarks()
            ->where('title_bookmarks.type', $bookmarkType)
            ->join('titles', 'title_bookmarks.title_id', '=', 'titles.id')
            ->select([
                'title_bookmarks.updated_at',
                'title_bookmarks.title_id',
                'titles.slug',
                'titles.title',
                'titles.released_at',
                'titles.last_episode',
                'titles.shikimori_rating',
                'titles.shikimori_votes',
                'titles.type',
            ]);

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
            'updated_at' => $query->orderBy('updated_at', $order),
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
        }
    }
}
