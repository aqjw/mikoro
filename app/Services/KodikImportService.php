<?php

namespace App\Services;

use App\Enums\TitleKind;
use App\Enums\TitleState;
use App\Enums\TitleStatus;
use App\Enums\TitleType;
use App\Enums\TranslationType;
use App\Jobs\StoreEpisodeMediaJob;
use App\Jobs\StoreTitleMediaJob;
use App\Models\Country;
use App\Models\Genre;
use App\Models\Studio;
use App\Models\Title;
use App\Models\Translation;
use Carbon\Carbon;
use Illuminate\Support\Str;

class KodikImportService
{
    public array $mappedGenres = [
        'Эротика' => 'Этти',
    ];

    public array $skipGenres = [
        'Юри',
        'Хентай',
        'Яой',
        'Детское',
    ];

    public function updateOrCreateTitle(array $data): Title
    {
        $material_data = $data['material_data'];

        $type = TitleType::fromName($data['type']);
        $kind = TitleKind::fromName($material_data['anime_kind']);
        if (! $kind) {
            $kind = TitleType::Anime->is($type) ? TitleKind::Movie : TitleKind::ONA;
        }

        $title = Title::updateOrCreate([
            'shikimori_id' => $data['shikimori_id'],
        ], [
            'kind' => $kind,
            'type' => $type,
            'title' => $data['title'],
            'title_orig' => $data['title_orig'],
            'other_title' => $data['other_title'] ?? '',
            'description' => $material_data['description'] ?? null,
            'duration' => $material_data['duration'] ?? null,
            'status' => TitleStatus::fromName($material_data['all_status'] ?? null, TitleStatus::Released),
            'year' => $data['year'],
            'released_at' => rescue(fn () => Carbon::parse(
                $material_data['released_at'] ?? $material_data['aired_at']
            ), report: false),
            'shikimori_rating' => $material_data['shikimori_rating'] ?? 0,
            'shikimori_votes' => $material_data['shikimori_votes'] ?? 0,
            'blocked_countries' => $data['blocked_countries'] ?? [],
            'blocked_seasons' => $data['blocked_seasons'] ?? [],
        ]);

        if (! $title->last_episode_at) {
            if (isset($data['updated_at'])) {
                $datetime = Carbon::parse($data['updated_at']);
                $title->last_episode_at = $datetime->setTimezone('UTC');
            }
            $title->last_episode_at ??= now()->setTimezone('UTC');
        }

        if ($title->wasRecentlyCreated) {
            $title->slug = $this->getSlug($data['title']);
            $title->last_episode = $data['last_episode'] ?? null;
            $title->episodes_count = $data['episodes_count'] ?? null;
            $title->state = TitleState::Active;
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

    public function getSlug(string $title): string
    {
        $originSlug = Str::slug($title);
        $slug = $originSlug;

        $counter = 1;

        while (Title::where('slug', $slug)->exists()) {
            $slug = "{$originSlug}-{$counter}";
            $counter++;
        }

        return $slug;
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
            $episode = $title->episodes()->updateOrCreate([
                'name' => null,
                'translation_id' => $translation_id,
            ], ['source' => $data['link']]);

            if ($episode->wasRecentlyCreated) {
                $this->storeEpisodeMedia(
                    episodeId: $episode->id,
                    screenshots: array_slice($data['screenshots'], 0, 5)
                );
            }

            return;
        }

        foreach (array_values($data['seasons'] ?? []) as $season) {
            foreach ($season['episodes'] as $name => $episodeData) {
                $episode = $title->episodes()->updateOrCreate(
                    compact('name', 'translation_id'),
                    ['source' => $episodeData['link']]
                );

                if ($episode->wasRecentlyCreated) {
                    $this->storeEpisodeMedia(
                        episodeId: $episode->id,
                        screenshots: array_slice($episodeData['screenshots'], 0, 5)
                    );
                }
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

    public function storeEpisodeMedia(int $episodeId, array $screenshots): void
    {
        StoreEpisodeMediaJob::dispatch($episodeId, $screenshots);
    }

    public function storeTitleMedia(int $titleId, array $data): void
    {
        $posterUrl = $data['material_data']['poster_url'] ?? null;
        if ($posterUrl) {
            StoreTitleMediaJob::dispatch($titleId, $posterUrl);
        }
    }
}
