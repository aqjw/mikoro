<?php

namespace App\Services;

use App\Models\Studio;
use App\Models\Title;
use Illuminate\Support\Collection;

class SearchService
{
    public function title(string $query): Collection
    {
        return Title::search($query)
            ->query(function ($builder) {
                $builder
                    ->with([
                        'media' => fn ($query) => $query->where('collection_name', 'poster'),
                        'genres',
                    ]);
            })
            ->take(10)
            ->get();
    }

    public function studio(string $query): Collection
    {
        return Studio::search($query)
            ->take(6)
            ->get(['slug', 'name']);
    }
}
