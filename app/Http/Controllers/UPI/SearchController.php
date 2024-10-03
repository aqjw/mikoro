<?php

namespace App\Http\Controllers\UPI;

use App\Http\Controllers\Controller;
use App\Http\Resources\TitleSearchResource;
use App\Models\Studio;
use App\Models\Title;
use App\Services\SearchService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;

class SearchController extends Controller
{
    public function random(): JsonResponse
    {
        $titles = Title::with([
            'media' => fn ($query) => $query->where('collection_name', 'poster'),
            'genres',
        ])
            ->inRandomOrder()
            ->take(3)
            ->get();

        $studios = Studio::query()
            ->inRandomOrder()
            ->take(6)
            ->get(['slug', 'name']);

        return response()->json([
            'title' => TitleSearchResource::collection($titles),
            'studio' => $studios,
            'director' => [],
            'voice_actor' => [],
            'character' => [],
        ]);
    }

    public function query(string $type, string $query, SearchService $searchService): JsonResponse
    {
        abort_unless(in_array($type, [
            'title',
            'studio',
            'director',
            'voice_actor',
            'character',
        ]), 400);

        abort_if(strlen($query) < 3, 400);

        $query = Str::slug($query, separator: ' ');
        $items = $searchService->{$type}($query);
        $result = match ($type) {
            'title' => TitleSearchResource::collection($items),
            default => $items,
        };

        return response()->json($result);
    }
}
