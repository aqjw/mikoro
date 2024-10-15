<?php

namespace App\Services;

use App\Models\Title;
use App\Models\TitleRating;
use App\Models\User;

class TitleRatingService
{
    public function calculateAverageRating(TitleRating $titleRating): void
    {
        $averageRating = $titleRating->title->ratings()->avg('rating');
        $titleRating->title->update(['rating' => $averageRating / 100]);
    }

    public function vote(User $user, Title $title, int|float $value): void
    {
        $title->ratings()->updateOrCreate(
            ['user_id' => $user->id],
            ['rating' => $value]
        );
    }
}
