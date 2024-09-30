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
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class KodikService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function list()
    {
        $queryParams = [
            'token' => env('KODIK_API_KEY'),
            'has_field' => 'shikimori_id',
            'types' => 'anime-serial,anime',
            'year' => implode(',', range(2005, 2024)),
            'with_episodes' => true,
            'with_material_data' => true,
        ];
        $url = 'https://kodikapi.com/list?' . http_build_query($queryParams);

        while (true) {
            $response = Http::get($url);
            if (! $response->successful() || empty($items)) {
                break;
            }

            $url = $response->json('next_page');
            $items = $response->json('results');

            foreach ($items as $item) {
                // create title
                $title = $this->createTitle($item);

                // create translation
                $translation = $this->createTranslation($item);

                // create episodes
                $this->createEpisodes($title, $translation, $item);

                // create studios
                $studios = $this->createStudios($item);
                $title->studios()->sync($studios);

                // create genres
                $genres = $this->createGenres($item);
                $title->genres()->sync($genres);

                if ($title->wasRecentlyCreated) {
                    $this->storeMedia($title, $item);
                }
            }

            dd(Title::all());
            if (empty($url)) {
                break;
            }
        }
    }

    public function createTitle(array $data): Title
    {
        $material_data = $data['material_data'];
        return Title::updateOrCreate([
            'kid' => $data['id'],
        ], [
            'type' => TitleType::fromName($data['type']),
            'title' => $data['title'],
            'title_orig' => $data['title_orig'],
            'other_title' => $data['other_title'],
            'description' => $material_data['description'] ?? null,
            'duration' => $material_data['duration'] ?? null,
            'status' => TitleStatus::fromName($material_data['all_status'] ?? null),
            'year' => $data['year'],
            'shikimori_id' => $data['shikimori_id'],
            'shikimori_rating' => $material_data['shikimori_rating'] ?? 0,
            // 'group_id' => $data['group_id'],
            'blocked_countries' => $data['blocked_countries'],
            'blocked_seasons' => $data['blocked_seasons'],
            'last_episode' => $data['last_episode'],
            'episodes_count' => $data['episodes_count'],
        ]);
    }

    public function createTranslation(array $data): Translation
    {
        return Translation::updateOrCreate(
            ['id' => $data['translation']['id']],
            [
                'title' => $data['translation']['title'],
                'type' => TranslationType::fromName($data['translation']['type'])
            ]
        );
    }

    public function createEpisodes(Title $title, Translation $translation, array $data): void
    {
        foreach (array_values($data['seasons']) as $season) {
            foreach ($season['episodes'] as $name => $source) {
                $title->episodes()->updateOrCreate(
                    ['name' => $name],
                    [
                        'source' => $source,
                        'translation_id' => $translation->id,
                    ],
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
            ->map(fn ($name) => Genre::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            ))
            ->pluck('id')
            ->toArray();
    }

    public function storeMedia(Title $title, array $data): void
    {
        StoreMediaJob::dispatch($title, $data);
    }
}
