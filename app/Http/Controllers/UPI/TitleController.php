<?php

namespace App\Http\Controllers\UPI;

use App\Enums\TranslationType;
use App\Http\Controllers\Controller;
use App\Http\Requests\UPI\PlaybackStateRequest;
use App\Http\Resources\TitleShortResource;
use App\Models\Country;
use App\Models\Episode;
use App\Models\Genre;
use App\Models\Studio;
use App\Models\Title;
use App\Models\Translation;
use App\Services\ActivityHistoryService;
use App\Services\CatalogService;
use App\Services\EpisodeReleaseSubscriptionService;
use App\Services\TitleRatingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TitleController extends Controller
{
    public function catalog(Request $request, CatalogService $catalogService): JsonResponse
    {
        $sorting = $request->sorting;
        $filters = $request->filters;
        $filterKeys = ['genres', 'studios', 'countries', 'translations', 'years'];

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
            'genres' => Genre::query()
                ->select('genres.id', 'genres.name', DB::raw('COUNT(*) as titles_count'))
                ->leftJoin('genre_title', 'genres.id', '=', 'genre_title.genre_id')
                ->groupBy('genres.id')
                ->having('titles_count', '>', 0)
                ->orderByDesc('titles_count')
                ->get(),

            'studios' => Studio::query()
                ->select('studios.id', 'studios.name', DB::raw('COUNT(*) as titles_count'))
                ->leftJoin('studio_title', 'studios.id', '=', 'studio_title.studio_id')
                ->groupBy('studios.id')
                ->having('titles_count', '>', 0)
                ->orderByDesc('titles_count')
                ->get(),

            'countries' => Country::query()
                ->select('countries.id', 'countries.name', DB::raw('COUNT(*) as titles_count'))
                ->leftJoin('country_title', 'countries.id', '=', 'country_title.country_id')
                ->groupBy('countries.id')
                ->having('titles_count', '>', 0)
                ->orderByDesc('titles_count')
                ->get(),

            'translations' => Translation::query()
                ->select('translations.id', 'translations.title', DB::raw('COUNT(*) as titles_count'))
                ->leftJoin('title_translation', 'translations.id', '=', 'title_translation.translation_id')
                ->groupBy('translations.id')
                ->having('titles_count', '>', 0)
                ->orderByDesc('titles_count')
                ->get(),

            'years' => Title::query()
                ->select('year', DB::raw('COUNT(*) as titles_count'))
                ->groupBy('year')
                ->orderByDesc('year')
                ->get()
                ->map(fn ($item) => [
                    'id' => $item->year,
                    'name' => $item->year,
                    'titles_count' => $item->titles_count,
                ]),
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
        $title->load('episodes', 'translations');

        $episodes = $title->episodes->map(fn ($episode) => [
            'id' => $episode->id,
            'label' => $episode->name,
            'source' => $episode->source,
            'translation_id' => $episode->translation_id,
        ]);

        $translations = $title->translations
            ->map(fn ($translation) => [
                'id' => $translation->id,
                'label' => $translation->title,
                'episodes_count' => $episodes
                    ->where('translation_id', $translation->id)
                    ->count(),
                'type' => $translation->type,
            ])
            ->sort(function ($a, $b) {
                // Sort by episode count in descending order
                if ($a['episodes_count'] !== $b['episodes_count']) {
                    return $b['episodes_count'] <=> $a['episodes_count'];
                }
                // If episode count is the same, sort by type
                if ($a['type'] !== $b['type']) {
                    return $a['type'] === TranslationType::Voice ? -1 : 1;
                }

                // If episode count and type are the same, sort by label
                return strcmp($a['label'], $b['label']);
            })
            ->select('id', 'label', 'episodes_count')
            ->values();

        $user = $request->user();
        $playbackState = $user?->playbackStates()
            ->where('title_id', $title->id)
            ->first();

        return response()->json([
            'single_episode' => $title->singleEpisode,
            'translations' => $translations,
            'episodes' => $episodes,
            'playback_state' => $playbackState ? [
                'episode_id' => $playbackState->episode_id,
                'translation_id' => $playbackState->translation_id,
                'time' => $playbackState->time,
            ] : null,
        ]);
    }

    public function rating(Title $title, Request $request, TitleRatingService $titleRatingService): JsonResponse
    {
        $data = $request->validate([
            'value' => ['required', 'numeric', 'min:0.5', 'max:10'],
        ]);

        $titleRatingService->vote(
            user: $request->user(),
            title: $title,
            value: $data['value']
        );

        $title = $title->fresh();

        return response()->json([
            'rating' => $title->rating,
            'rating_votes' => $title->ratings()->count(),
        ]);
    }

    public function playbackState(Title $title, PlaybackStateRequest $request, ActivityHistoryService $activityHistoryService): JsonResponse
    {
        $data = $request->validated();
        $user = $request->user();

        $user->playbackStates()
            ->updateOrCreate(
                ['title_id' => $title->id],
                [
                    'episode_id' => $data['episode_id'],
                    'translation_id' => $data['translation_id'],
                    'time' => $data['time'],
                ]
            );

        $activityHistoryService->storeEpisodeOrNone(
            user: $user,
            titleId: $title->id,
            episodeId: $data['episode_id'],
        );

        return response()->json('ok');
    }

    public function episodeSubscriptionToggle(Title $title, Request $request, EpisodeReleaseSubscriptionService $service): JsonResponse
    {
        $data = $request->validate([
            'value' => ['nullable', 'array', 'exists:translations,id'],
        ]);

        $service->subscribe(
            user: $request->user(),
            titleId: $title->id,
            translationIds: $data['value'] ?? []
        );

        return response()->json('ok');
    }
}
