<?php

namespace App\Http\Controllers\UPI;

use App\Enums\TitleStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\TitleShortResource;
use App\Models\Country;
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
        $filterKeys = ['genres', 'studios', 'countries', 'translations', 'years', 'statuses'];

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
            'total' => $result->total(),
            'has_more' => $result->hasMorePages(),
        ]);
    }

    public function filters(): JsonResponse
    {
        return response()->json([
            'genres' => Genre::query()->orderBy('name')->get(['id', 'name']),
            'studios' => Studio::query()->orderBy('name')->get(['id', 'name']),
            'countries' => Country::query()->orderBy('name')->get(['id', 'name']),
            'translations' => Translation::query()->orderBy('title')->get(['id', 'title']),
            'years' => Title::query()
                ->groupBy('year')
                ->orderByDesc('year')
                ->pluck('year')->map(fn ($year) => [
                    'id' => $year,
                    'name' => $year,
                ]),
            'statuses' => TitleStatus::getCases(),
        ]);
    }

    public function genres(): JsonResponse
    {
        return response()->json([
            'genres' => Genre::query()->orderBy('name')->get(['slug', 'name']),
        ]);
    }

    public function episodes(Title $title, Request $request): JsonResponse
    {
        $title->load('episodes.translation');

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
            'playback_state' => $playbackState ? [
                'episode_id' => $playbackState->episode_id,
                'translation_id' => $playbackState->translation_id,
                'time' => $playbackState->time,
            ] : null,
        ]);
    }

    public function playbackState(Title $title, Request $request): JsonResponse
    {
        $request->validate([
            'episode_id' => ['required'],
            'translation_id' => ['required'],
            'time' => ['required'],
        ]);

        $request->user()
            ->playbackStates()
            ->updateOrCreate(
                ['title_id' => $title->id],
                [
                    'episode_id' => $request->episode_id,
                    'translation_id' => $request->translation_id,
                    'time' => $request->time,
                ]
            );

        return response()->json('ok');
    }
}
