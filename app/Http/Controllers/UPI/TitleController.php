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
}
