<?php

namespace App\Http\Controllers\UPI;

use App\Enums\TitleType;
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
use App\Services\CatalogService;
use App\Services\EpisodeReleaseSubscriptionService;
use App\Services\KodikService;
use App\Services\PlaybackStateService;
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

    public function recommendations(Title $title, Request $request): JsonResponse
    {
        $genreIds = $title->genres()->pluck('id');

        $titles = Title::query()
            ->with([
                'media' => fn ($query) => $query->where('collection_name', 'poster'),
            ])
            ->where('id', '!=', $title->id)
            ->where('type', TitleType::AnimeSerial)
            ->when($title->group_id, fn ($query) => $query->where('group_id', '!=', $title->group_id))
            ->whereHas('genres', fn ($query) => $query->whereIn('id', $genreIds))
            ->withCount([
                'genres as genre_match_count' => fn ($query) => $query->whereIn('id', $genreIds),
            ])
            ->orderByDesc('genre_match_count')
            ->orderByDesc('shikimori_rating')
            ->groupBy('group_id')
            ->take(7)
            ->get();

        return response()->json([
            'items' => TitleShortResource::collection($titles),
        ]);
    }

    public function episodes(Title $title): JsonResponse
    {
        $title->load('episodes', 'translations');

        $episodes = $title->episodes->map(fn ($episode) => [
            'id' => $episode->id,
            'label' => $episode->name,
            'translation_id' => $episode->translation_id,
        ]);

        $translations = $title->translations
            ->map(fn ($translation) => [
                'id' => $translation->id,
                'label' => $translation->title,
                'episodes_count' => $episodes
                    ->where('translation_id', $translation->id)
                    ->sortByDesc('label')
                    ->value('label'),
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

        return response()->json([
            'translations' => $translations,
            'episodes' => $episodes,
        ]);
    }

    public function videoLinks(Title $title, Request $request, KodikService $kodikService, ?Episode $episode = null): JsonResponse
    {
        sleep(3);
        $episode ??= $title->episodes()->first();
        $links = cache()->remember(
            key: "episode-links-{$episode->id}",
            ttl: 60 * 60 * 6, // 6 hours
            callback: fn () => $kodikService->getVideoLinks(
                link: $episode->source,
                userIp: $request->ip()
            )
        );

        return response()->json(['links' => $links]);
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

    public function getPlaybackState(Title $title, Request $request, PlaybackStateService $playbackStateService): JsonResponse
    {
        $playbackState = $playbackStateService->get(
            user: $request->user(),
            title: $title
        );

        return response()->json([
            'episode_id' => $playbackState->episode_id,
            'translation_id' => $playbackState->translation_id,
            'time' => $playbackState->time,
        ]);
    }

    public function savePlaybackState(Title $title, PlaybackStateRequest $request, PlaybackStateService $playbackStateService): JsonResponse
    {
        $playbackStateService->save(
            user: $request->user(),
            title: $title,
            data: $request->validated()
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
