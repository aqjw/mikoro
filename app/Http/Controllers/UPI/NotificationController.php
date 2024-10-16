<?php

namespace App\Http\Controllers\UPI;

use App\Http\Controllers\Controller;
use App\Http\Resources\NotificationResource;
use App\Models\Title;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $items = $user->unreadNotifications()->latest()->take(100)->get();

        if ($items->isNotEmpty()) {
            $titleIds = $items->pluck('data.title_id')->unique();
            $titles = Title::query()
                ->with(['media' => fn ($query) => $query->where('collection_name', 'poster')])
                ->whereIn('id', $titleIds)
                ->get(['id', 'slug']);

            $items->map(function ($item) use ($titles) {
                $title = $titles->where('id', $item->data['title_id'])->first();
                if ($title) {
                    $item->extra = [
                        'image' => MediaService::getImageDetails($title->media[0] ?? null, true),
                        'title_slug' => $title->slug,
                    ];
                }

                return $item;
            });
        }

        return response()->json([
            'items' => NotificationResource::collection($items),
            'unread_count' => $user->unreadNotifications()->count(),
        ]);
    }

    public function read(DatabaseNotification $notification): JsonResponse
    {
        abort_unless($notification->notifiable_id = auth()->id(), 403);
        $notification->markAsRead();

        return response()->json('ok');
    }

    public function readAll(Request $request): JsonResponse
    {
        $request
            ->user()
            ->notifications()
            ->unread()
            ->update(['read_at' => now()]);

        return response()->json('ok');
    }
}
