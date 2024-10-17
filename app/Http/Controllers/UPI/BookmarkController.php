<?php

namespace App\Http\Controllers\UPI;

use App\Enums\BookmarkType;
use App\Http\Controllers\Controller;
use App\Http\Resources\BookmarkResource;
use App\Models\Title;
use App\Services\BookmarkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookmarkController extends Controller
{
    public function index(string $bookmark, Request $request, BookmarkService $bookmarkService): JsonResponse
    {
        $limit = 15;
        $sortBy = $request->sortBy[0] ?? null;

        $result = $bookmarkService->get(
            user: $request->user(),
            bookmarkType: BookmarkType::fromName($bookmark, BookmarkType::Planned),
            sorting: [
                'key' => $sortBy['key'] ?? null,
                'order' => $sortBy['order'] ?? null,
            ],
            limit: $limit
        );

        return response()->json([
            'items' => BookmarkResource::collection($result->items()),
            'total' => $result->total(),
            'items_per_page' => $limit,
        ]);
    }

    public function toggle(Title $title, Request $request, BookmarkService $bookmarkService): JsonResponse
    {
        $data = $request->validate([
            'value' => ['nullable', Rule::enum(BookmarkType::class)],
        ]);

        $bookmarkService->toggle(
            user: $request->user(),
            titleId: $title->id,
            type: $data['value'] ?? null
        );

        return response()->json('ok');
    }
}
