<?php

namespace App\Services;

use App\Models\Title;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class CatalogService
{
    public function get(array $sorting, array $filters, int $limit = 28): LengthAwarePaginator
    {
        $query = Title::query()
            ->with([
                'media' => fn ($query) => $query->where('collection_name', 'poster'),
                'genres',
            ]);

        $this->filters($query, $filters);
        $this->sorting($query, $sorting);

        return $query->paginate($limit);
    }

    public function sorting(Builder $query, array $data): void
    {
        match ($data['option']) {
            'latest' => $query->orderBy('updated_at', $data['dir']),
            'rating' => $query->orderBy('shikimori_rating', $data['dir']),
            'comments' => $query,
            'episodes_count' => $query->orderBy('last_episode', $data['dir']),
            'seasons_count' => $query->withCount('related')->orderBy('related_count', $data['dir']),
            default => null
        };
    }

    public function filters(Builder $query, array $data): void
    {
        $query->where(function ($query) use ($data) {
            foreach ($data as $filter => $cases) {
                $incl = $cases['incl'];
                $excl = $cases['excl'];

                match ($filter) {
                    'genres' => $query
                        ->when(
                            filled($incl),
                            fn ($query) => $query->whereHas('genres', fn ($query) => $query->whereIn('genres.id', $incl))
                        )
                        ->when(
                            filled($excl),
                            fn ($query) => $query->whereDoesntHave('genres', fn ($query) => $query->whereIn('genres.id', $excl))
                        ),
                    'studios' => $query
                        ->when(
                            filled($incl),
                            fn ($query) => $query->whereHas('studios', fn ($query) => $query->whereIn('studios.id', $incl))
                        )
                        ->when(
                            filled($excl),
                            fn ($query) => $query->whereDoesntHave('studios', fn ($query) => $query->whereIn('studios.id', $excl))
                        ),
                    'translations' => $query
                        ->when(
                            filled($incl),
                            fn ($query) => $query->whereHas('episodes', fn ($query) => $query->whereIn('translation_id', $incl))
                        )
                        ->when(
                            filled($excl),
                            fn ($query) => $query->whereDoesntHave('episodes', fn ($query) => $query->whereIn('translation_id', $excl))
                        ),
                    'years' => $query
                        ->when(
                            filled($incl),
                            fn ($query) => $query->whereIn('year', $incl)
                        )
                        ->when(
                            filled($excl),
                            fn ($query) => $query->whereNotIn('year', $excl)
                        ),
                    'statuses' => $query
                        ->when(
                            filled($incl),
                            fn ($query) => $query->whereIn('status', $incl)
                        )
                        ->when(
                            filled($excl),
                            fn ($query) => $query->whereNotIn('status', $excl)
                        ),
                    default => null
                };
            }
        });
    }
}
