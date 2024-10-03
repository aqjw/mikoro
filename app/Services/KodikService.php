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
        $url = 'https://kodikapi.com/list?'.http_build_query($queryParams);

        while (true) {
            $response = Http::get($url);
            $url = $response->json('next_page');
            $items = $response->json('results');

            if (! $response->successful() || empty($items)) {
                break;
            }

            foreach ($items as $item) {
                // create title
                $title = $this->createTitle($item);

                // create translation
                $translation = $this->createTranslation($item);

                // create episodes
                $this->createEpisodes($title, $translation, $item);

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

            dd(Title::all());
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
            'other_title' => $data['other_title'],
            'description' => $material_data['description'] ?? null,
            'duration' => $material_data['duration'] ?? null,
            'status' => TitleStatus::fromName($material_data['all_status'] ?? null),
            'year' => $data['year'],
            'shikimori_rating' => $material_data['shikimori_rating'] ?? 0,
            // 'group_id' => $data['group_id'],
            'blocked_countries' => $data['blocked_countries'],
            'blocked_seasons' => $data['blocked_seasons'],
        ]);

        if ($title->wasRecentlyCreated) {
            $title->last_episode = $data['last_episode'];
            $title->episodes_count = $data['episodes_count'];
            $title->save();
        } else {
            $title->last_episode = $data['last_episode'] > $title->last_episode
                ? $data['last_episode']
                : $title->last_episode;

            $title->episodes_count = $data['episodes_count'] > $title->episodes_count
                ? $data['episodes_count']
                : $title->episodes_count;

            $title->save();
        }
    }

    public function createTranslation(array $data): Translation
    {
        return Translation::firstOrCreate(
            ['id' => $data['translation']['id']],
            [
                'slug' => Str::slug($data['translation']['title']),
                'title' => $data['translation']['title'],
                'type' => TranslationType::fromName($data['translation']['type']),
            ]
        );
    }

    public function createEpisodes(Title $title, Translation $translation, array $data): void
    {
        foreach (array_values($data['seasons']) as $season) {
            foreach ($season['episodes'] as $name => $source) {
                $title->episodes()->updateOrCreate(
                    [
                        'name' => $name,
                        'translation_id' => $translation->id,
                    ],
                    ['source' => $source],
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

    public function storeMedia(int $titleId, array $data): void
    {
        StoreMediaJob::dispatch($titleId, [
            'poster' => $data['material_data']['poster_url'] ?? null,
            'screenshots' => $data['material_data']['screenshots'] ?? [],
        ]);
    }
}
