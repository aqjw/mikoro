<?php

namespace App\Http\Controllers\UPI;

use App\Enums\BookmarkType;
use App\Http\Controllers\Controller;
use App\Http\Resources\ActivityHistoryResource;
use App\Http\Resources\BookmarkCardResource;
use App\Models\User;
use App\Services\ActivityHistoryService;
use App\Services\BookmarkService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function bookmarks(User $user, string $bookmark, Request $request, BookmarkService $bookmarkService): JsonResponse
    {
        $columns = ['latest' => 'desc', 'oldest' => 'asc'];

        $result = $bookmarkService->get(
            user: $user,
            bookmarkType: BookmarkType::fromName($bookmark, BookmarkType::Planned),
            withMedia: true,
            sorting: [
                'key' => 'updated_at',
                'order' => $columns[$request->sorting] ?? 'desc',
            ],
            limit: 24
        );

        return response()->json([
            'items' => BookmarkCardResource::collection($result->items()),
            'total' => $result->total(),
            'has_more' => $result->hasMorePages(),
        ]);
    }

    public function activityHistories(User $user, ActivityHistoryService $activityHistoryService): JsonResponse
    {
        $result = $activityHistoryService->get(
            user: $user,
            limit: 4
        );

        return response()->json([
            'items' => ActivityHistoryResource::collection($result->items()),
            'total' => $result->total(),
            'has_more' => $result->hasMorePages(),
        ]);
    }

    public function heatmap(User $user, ActivityHistoryService $activityHistoryService): JsonResponse
    {
        $result = $activityHistoryService->heatmap($user);

        return response()->json([
            'items' => $result,
        ]);
    }
}
