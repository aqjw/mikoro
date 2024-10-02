<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Resources\TitleShortResource;
use App\Models\Title;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $titles = Title::with([
            'media' => fn ($query) => $query->where('collection_name', 'poster'),
            // 'genres',
        ])
            ->get();

        return Inertia::render('Home', [
            'titles' => TitleShortResource::collection($titles),
        ]);
    }
}
