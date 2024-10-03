<?php

namespace App\Http\Controllers;

use App\Http\Resources\TitleShortResource;
use App\Models\Title;
use Illuminate\Http\Request;
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
