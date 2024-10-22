<?php

namespace App\Http\Controllers\UPI;

use App\Http\Controllers\Controller;
use App\Http\Requests\UPI\SettingsRequest;
use Illuminate\Http\JsonResponse;

class SettingsController extends Controller
{
    public function __invoke(SettingsRequest $request): JsonResponse
    {
        $request->user()->update([
            'settings' => $request->validated('settings'),
        ]);

        return response()->json('ok');
    }
}
