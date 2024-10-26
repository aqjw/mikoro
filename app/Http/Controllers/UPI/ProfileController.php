<?php

namespace App\Http\Controllers\UPI;

use App\Enums\BookmarkType;
use App\Http\Controllers\Controller;
use App\Http\Requests\UPI\UpdateProfileInformationRequest;
use App\Http\Resources\ActivityHistoryResource;
use App\Http\Resources\BookmarkCardResource;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\ActivityHistoryService;
use App\Services\BookmarkService;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

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

    public function updateInformation(UpdateProfileInformationRequest $request, UserService $userService)
    {
        $userService->updateInfo(
            user: $request->user(),
            data: $request->validated()
        );

        return back();
    }

    public function updateAvatar(Request $request): JsonResponse
    {
        $data = $request->validate([
            'base64' => ['required', 'string'],
        ]);

        /** @var User $user */
        $user = $request->user();
        $user->media()->delete();
        $user->addMediaFromBase64($data['base64'])->toMediaCollection('avatar');

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    public function deleteAvatar(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->media()->delete();

        return response()->json([
            'user' => new UserResource($user),
        ]);
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        // TODO:
        throw ValidationException::withMessages([
            'message' => 'error',
        ]);
    }
}
