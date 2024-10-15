<?php

namespace App\Console\Commands;

use App\Services\KodikService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class KodikLoadTitles extends Command
{
    protected $signature = 'app:kodik-load-titles';

    protected $description = 'Load titles from Kodik API';

    public function handle(KodikService $kodikService)
    {
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

    private function processItem(KodikService $kodikService, array $item): void
    {
        $title = $kodikService->createTitle($item);
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
            'token' => env('KODIK_API_KEY'),
            'has_field' => 'shikimori_id',
            'types' => 'anime-serial,anime',
            'year' => implode(',', range(2005, 2024)),
            'with_episodes_data' => true,
            'limit' => 100,
            'with_material_data' => true,
        ];
    }
}
