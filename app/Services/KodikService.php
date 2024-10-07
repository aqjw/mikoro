<?php

namespace App\Services;

use App\Enums\TitleStatus;
use App\Enums\TitleType;
use App\Enums\TranslationType;
use App\Jobs\StoreMediaJob;
use App\Models\Genre;
use App\Models\Studio;
use App\Models\Title;
use App\Models\Translation;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class KodikService
{
    public array $mappedGenres = [
        'Эротика' => 'Этти',
    ];

    public array $skipGenres = [
        'Юри',
        'Хентай',
        'Яой',
    ];

    public function list()
    {
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ignore_user_abort(true);

        $queryParams = [
            'token' => env('KODIK_API_KEY'),
            'has_field' => 'shikimori_id',
            'types' => 'anime-serial,anime',
            'year' => implode(',', range(2005, 2024)),
            'with_episodes' => true,
            'limit' => 100,
            'with_material_data' => true,
        ];
        $url = 'https://kodikapi.com/list?'.http_build_query($queryParams);
        // dd($url);

        while (true) {
            $response = Http::get($url);
            $url = $response->json('next_page');
            $items = $response->json('results');

            if (! $response->successful() || empty($items)) {
                break;
            }

            foreach ($items as $item) {
                if ($this->shouldSkip($item)) {
                    continue;
                }

                // create title
                $title = $this->createTitle($item);

                // create translation
                $translationId = $this->createTranslation($item);

                // create episodes
                $this->createEpisodes($title, $translationId, $item);

                if ($title->wasRecentlyCreated) {
                    // create studios
                    $studios = $this->createStudios($item);
                    $title->studios()->sync($studios);

                    // create genres
                    $genres = $this->createGenres($item);
                    $title->genres()->sync($genres);

                    // store media
                    $this->storeMedia($title->id, $item);
                }
            }

            if (Title::count() > 100) {
                break;
            }
            if (empty($url)) {
                break;
            }
        }
    }

    public function createTitle(array $data): Title
    {
        $material_data = $data['material_data'];
        $title = Title::updateOrCreate([
            'shikimori_id' => $data['shikimori_id'],
        ], [
            'slug' => Str::slug($data['title']),
            'type' => TitleType::fromName($data['type']),
            'title' => $data['title'],
            'title_orig' => $data['title_orig'],
            'other_title' => $data['other_title'] ?? '',
            'description' => $material_data['description'] ?? null,
            'duration' => $material_data['duration'] ?? null,
            'status' => TitleStatus::fromName($material_data['all_status'] ?? null),
            'year' => $data['year'],
            'shikimori_rating' => $material_data['shikimori_rating'] ?? 0,
            // 'group_id' => $data['group_id'],
            'blocked_countries' => $data['blocked_countries'] ?? [],
            'blocked_seasons' => $data['blocked_seasons'] ?? [],
        ]);

        if (isset($data['updated_at'])) {
            $updated_at = Carbon::parse($data['updated_at']);
            if (! $title->updated_at || $updated_at->greaterThan($title->updated_at)) {
                $title->updated_at = $updated_at;
            }
        }

        $title->updated_at ??= now();

        if ($title->wasRecentlyCreated) {
            $title->last_episode = $data['last_episode'] ?? null;
            $title->episodes_count = $data['episodes_count'] ?? null;
            $title->save();
        } else {
            if (isset($data['last_episode'])) {
                $title->last_episode = $data['last_episode'] > $title->last_episode
                    ? $data['last_episode']
                    : $title->last_episode;
            }

            if (isset($data['episodes_count'])) {
                $title->episodes_count = $data['episodes_count'] > $title->episodes_count
                    ? $data['episodes_count']
                    : $title->episodes_count;
            }
        }

        $title->save();

        return $title;
    }

    public function createTranslation(array $data): int
    {
        $translation = $data['translation'];
        Translation::firstOrCreate(
            ['id' => $translation['id']],
            [
                'slug' => Str::slug($translation['title']),
                'title' => $translation['title'],
                'type' => TranslationType::fromName($translation['type']),
            ]
        );

        return (int) $translation['id'];
    }

    public function createEpisodes(Title $title, int $translation_id, array $data): void
    {
        foreach (array_values($data['seasons'] ?? []) as $season) {
            foreach ($season['episodes'] as $name => $source) {
                $title->episodes()->updateOrCreate(
                    compact('name', 'translation_id'),
                    compact('source')
                );
            }
        }
    }

    public function createStudios(array $data): array
    {
        return collect($data['material_data']['anime_studios'] ?? [])
            ->map(fn ($name) => Studio::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            ))
            ->pluck('id')
            ->toArray();
    }

    public function createGenres(array $data): array
    {
        return collect($data['material_data']['anime_genres'] ?? [])
            ->map(function ($name) {
                $name = $this->mappedGenres[$name] ?? $name;

                return Genre::firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => $name]
                );
            })
            ->pluck('id')
            ->toArray();
    }

    public function shouldSkip(array $data): bool
    {
        $skipGenre = collect($data['material_data']['anime_genres'] ?? [])
            ->some(fn ($genre) => in_array($genre, $this->skipGenres));

        return $skipGenre;
    }

    public function storeMedia(int $titleId, array $data): void
    {
        StoreMediaJob::dispatch($titleId, [
            'poster' => $data['material_data']['poster_url'] ?? null,
            'screenshots' => $data['material_data']['screenshots'] ?? [],
        ]);
    }
}
