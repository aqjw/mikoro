<?php

namespace App\Http\Controllers;

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    public function profile(Request $request, ?User $user = null): Response
    {
        $user ??= $request->user();

        return Inertia::render('Profile/Index', [
            'user' => new UserResource($user),
        ]);
    }

    public function edit(Request $request): Response
    {
        return Inertia::render('Profile/Edit', [
            'user' => new UserResource($request->user()),
        ]);
    }
}
