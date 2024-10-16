<?php

namespace App\Services;

use App\Models\User;

class BookmarkService
{
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
