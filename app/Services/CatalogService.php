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
            ])
            ->select('titles.*')
            ->groupBy('titles.id');

        $this->filters($query, $filters);
        $this->sorting($query, $sorting);

        return $query->paginate($limit);
    }

    public function sorting(Builder $query, array $data): void
    {
        match ($data['option']) {
            'latest' => $query->orderBy('last_episode_at', $data['dir']),
            // TODO: sort by rating when we have data
            'rating' => $query->orderBy('shikimori_rating', $data['dir']),
            'comments_count' => $query->withCount('comments')->orderBy('comments_count', $data['dir']),
            'episodes_count' => $query->orderBy('last_episode', $data['dir']),
            'seasons_count' => $query->withCount('related')->orderBy('related_count', $data['dir']),
            default => null
        };
    }

    public function filters(Builder $query, array $data): void
    {
        foreach ($data as $filter => $cases) {
            $incl = $cases['incl'];
            $excl = $cases['excl'];

            match ($filter) {
                'genres' => $query
                    ->when(filled($incl) || filled($excl), fn ($query) => $query->join('genre_title', 'titles.id', '=', 'genre_title.title_id')),
                'studios' => $query
                    ->when(filled($incl) || filled($excl), fn ($query) => $query->join('studio_title', 'titles.id', '=', 'studio_title.title_id')),
                'countries' => $query
                    ->when(filled($incl) || filled($excl), fn ($query) => $query->join('country_title', 'titles.id', '=', 'country_title.title_id')),
                'translations' => $query
                    ->when(filled($incl) || filled($excl), fn ($query) => $query->join('title_translation', 'titles.id', '=', 'title_translation.title_id')),
                default => null,
            };
        }

        $query->where(function ($query) use ($data) {
            foreach ($data as $filter => $cases) {
                $incl = $cases['incl'];
                $excl = $cases['excl'];

                match ($filter) {
                    'genres' => $query
                        ->when(filled($incl), fn ($query) => $query->whereIn('genre_title.genre_id', $incl))
                        ->when(filled($excl), fn ($query) => $query->whereNotIn('genre_title.genre_id', $excl)),
                    'studios' => $query
                        ->when(filled($incl), fn ($query) => $query->whereIn('studio_title.studio_id', $incl))
                        ->when(filled($excl), fn ($query) => $query->whereNotIn('studio_title.studio_id', $excl)),
                    'countries' => $query
                        ->when(filled($incl), fn ($query) => $query->whereIn('country_title.country_id', $incl))
                        ->when(filled($excl), fn ($query) => $query->whereNotIn('country_title.country_id', $excl)),
                    'translations' => $query
                        ->when(filled($incl), fn ($query) => $query->whereIn('title_translation.translation_id', $incl))
                        ->when(filled($excl), fn ($query) => $query->whereNotIn('title_translation.translation_id', $excl)),
                    'years' => $query
                        ->when(filled($incl), fn ($query) => $query->whereIn('year', $incl))
                        ->when(filled($excl), fn ($query) => $query->whereNotIn('year', $excl)),
                    default => null
                };
            }
        });
    }
}
