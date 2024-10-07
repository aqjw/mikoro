<?php

namespace App\Http\Controllers\UPI;

use App\Enums\TitleStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\TitleShortResource;
use App\Models\Genre;
use App\Models\Studio;
use App\Models\Title;
use App\Models\Translation;
use App\Services\CatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TitleController extends Controller
{
    public function catalog(Request $request, CatalogService $catalogService): JsonResponse
    {
        $sorting = $request->sorting;
        $filters = $request->filters;
        $filterKeys = ['genres', 'studios', 'translations', 'years', 'statuses'];

        $result = $catalogService->get(
            sorting: [
                'option' => $sorting['option'] ?? 'latest',
                'dir' => $sorting['dir'] ?? 'desc',
            ],
            filters: collect($filterKeys)->mapWithKeys(fn ($key) => [
                $key => [
                    'incl' => $filters[$key]['incl'] ?? [],
                    'excl' => $filters[$key]['excl'] ?? [],
                ],
            ])->toArray()
        );

        return response()->json([
            'items' => TitleShortResource::collection($result->items()),
            'has_more' => $result->hasMorePages(),
        ]);
    }

    public function filters(Request $request): JsonResponse
    {
        return response()->json([
            'genres' => Genre::query()->get(['id', 'name']),
            'studios' => Studio::query()->get(['id', 'name']),
            'translations' => Translation::query()->get(['id', 'title']),
            'years' => Title::groupBy('year')->pluck('year')->map(fn ($year) => [
                'id' => $year,
                'name' => $year,
            ]),
            'statuses' => TitleStatus::getCases(),
        ]);
    }

    public function episodes(Title $title, Request $request): JsonResponse
    {
        $translations = $title->episodes
            ->groupBy('translation_id')
            ->map(fn ($episodes) => [
                'id' => $episodes->first()->translation->id,
                'label' => $episodes->first()->translation->title,
            ])
            ->values();

        $episodes = $title->episodes->map(fn ($episode) => [
            'id' => $episode->id,
            'label' => $episode->name,
            'source' => $episode->source,
            'translation_id' => $episode->translation_id,
        ]);

        $user = $request->user();
        $playbackState = $user?->playbackStates()
            ->where('title_id', $title->id)
            ->first();

        return response()->json([
            'translations' => $translations,
            'episodes' => $episodes,
            'playback_state' => [
                'translationId' => $playbackState->translation_id ?? $translations[0]['id'],
                'episodeId' => $playbackState->episode_id ?? $episodes[0]['id'],
                'time' => $playbackState->time ?? 0,
            ],
        ]);
    }
}
