<?php

namespace App\Services;

use App\Enums\TitleStatus;
use App\Enums\TitleType;
use App\Enums\TranslationType;
use App\Jobs\StoreMediaJob;
use App\Models\Country;
use App\Models\Genre;
use App\Models\Studio;
use App\Models\Title;
use App\Models\Translation;
use Carbon\Carbon;
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
            'status' => TitleStatus::fromName($material_data['all_status'] ?? null, TitleStatus::Released),
            'year' => $data['year'],
            'aired_at' => rescue(fn () => Carbon::parse($material_data['aired_at']), report: false),
            'shikimori_rating' => $material_data['shikimori_rating'] ?? 0,
            'blocked_countries' => $data['blocked_countries'] ?? [],
            'blocked_seasons' => $data['blocked_seasons'] ?? [],
        ]);

        if (isset($data['updated_at'])) {
            $updated_at = Carbon::parse($data['updated_at']);
            if (! $title->updated_at || $updated_at->greaterThan($title->updated_at)) {
                $title->updated_at = $updated_at->setTimezone('UTC');
            }
        }
        $title->updated_at ??= now()->setTimezone('UTC');

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

    public function syncTranslations(Title $title): void
    {
        $title->load('episodes:translation_id,title_id');
        $translationIds = $title->episodes->pluck('translation_id')->unique()->values();
        $title->translations()->sync($translationIds);
    }

    public function createEpisodes(Title $title, int $translation_id, array $data): void
    {
        if ($title->singleEpisode) {
            $title->episodes()->updateOrCreate([
                'name' => null,
                'translation_id' => $translation_id,
            ], ['source' => $data['link']]);

            return;
        }

        foreach (array_values($data['seasons'] ?? []) as $season) {
            foreach ($season['episodes'] as $name => $source) {
                $title->episodes()->updateOrCreate(
                    compact('name', 'translation_id'),
                    compact('source')
                );
            }
        }
    }

    public function createCountries(array $data): array
    {
        return collect($data['material_data']['countries'] ?? [])
            ->map(fn ($name) => Country::firstOrCreate(
                ['slug' => Str::slug($name)],
                ['name' => $name]
            ))
            ->pluck('id')
            ->toArray();
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
