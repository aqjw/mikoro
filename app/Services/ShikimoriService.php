<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class ShikimoriService
{
    public function getFranchise(int $shikimori_id): Collection
    {
        $response = Http::get("https://shikimori.one/api/animes/{$shikimori_id}/franchise");
        $result = $response->json('nodes');

        return collect($result)->values();
    }

    public function getRelated(int $shikimori_id): Collection
    {
        $response = Http::get("https://shikimori.one/api/animes/{$shikimori_id}/related");
        $result = $response->json();

        return collect($result)->pluck('anime')->filter()->values();
    }
}
