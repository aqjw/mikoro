<?php

namespace App\Http\Middleware;

use App\Http\Resources\NotificationResource;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        /** @var \App\Models\User */
        $user = $request->user();

        return [
            ...parent::share($request),
            'auth' => $user ? [
                'user' => $user,
                'notifications' => [
                    'unreadCount' => $user->unreadNotifications()->count(),
                    'lastItems' => NotificationResource::collection(
                        $user->unreadNotifications()->latest()->take(10)->get()
                    ),
                ],
            ] : null,
        ];
    }
}
