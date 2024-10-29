<?php

namespace App\Console\Commands;

use App\Enums\TitleKind;
use App\Models\Title;
use App\Services\KodikImportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class KodikLoadTitles extends Command
{
    protected $signature = 'app:kodik-load-titles';

    protected $description = 'Load titles from Kodik API';

    protected array $titleSearchColumns = [];

    public function handle(KodikImportService $kodikService)
    {
        $this->titleSearchColumns = array_keys(Title::make()->toSearchableArray());

        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ignore_user_abort(true);

        $url = 'https://kodikapi.com/list?'.http_build_query($this->getQueryParams());
        $bar = null;

        while ($url) {
            $data = $this->fetchKodikData($url);
            $url = $data['next_page'];
            $items = $data['results'];
            $total = $data['total'];

            if (empty($items)) {
                break;
            }

            if ($bar === null && $total > 0) {
                $bar = $this->output->createProgressBar($total);
                $bar->start();
            }

            foreach ($items as $item) {
                $bar->advance();

                if ($kodikService->shouldSkip($item)) {
                    continue;
                }

                $this->processItem($kodikService, $item);
            }

            if (empty($url)) {
                $bar->finish();
                break;
            }
        }
    }

    private function fetchKodikData($url): array
    {
        $response = Http::get($url);

        if ($response->failed()) {
            $this->error('Failed to fetch data from Kodik API.');

            return ['next_page' => null, 'results' => [], 'total' => 0];
        }

        return [
            'next_page' => $response->json('next_page'),
            'results' => $response->json('results'),
            'total' => $response->json('total'),
        ];
    }

    private function processItem(KodikImportService $kodikService, array $item): void
    {
        /** @var Title $title */
        $title = Title::withoutSyncingToSearch(
            fn () => $kodikService->updateOrCreateTitle($item)
        );

        $keys = array_keys($title->getChanges());
        $needsSync = count(array_intersect($keys, $this->titleSearchColumns)) > 0;
        if ($needsSync) {
            $title->searchable();
        }

        $translationId = $kodikService->createTranslation($item);
        $kodikService->createEpisodes($title, $translationId, $item);
        $kodikService->syncTranslations($title);

        if ($title->wasRecentlyCreated) {
            $studios = $kodikService->createStudios($item);
            $title->studios()->sync($studios);

            $countries = $kodikService->createCountries($item);
            $title->countries()->sync($countries);

            $genres = $kodikService->createGenres($item);
            $title->genres()->sync($genres);

            $kodikService->storeTitleMedia($title->id, $item);
        }
    }

    private function getQueryParams(): array
    {
        return [
            'token' => env('KODIK_API_PUBLIC_KEY'),
            // uncomment for initial load titles
            // 'sort' => 'updated_at',
            // 'order' => 'asc',
            //
            'has_field' => 'shikimori_id',
            'types' => 'anime-serial,anime',
            'anime_kind' => implode(',', array_keys(TitleKind::mapped())),
            'year' => implode(',', range(2005, 2024)),
            'duration' => '5-200',
            'limit' => 100,
            'with_episodes_data' => true,
            'with_material_data' => true,
        ];
    }
}
