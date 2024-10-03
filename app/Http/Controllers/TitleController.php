<?php

namespace App\Http\Controllers;

use App\Http\Resources\TitleShortResource;
use App\Models\Title;
use Inertia\Inertia;
use Inertia\Response;

class TitleController extends Controller
{
    public function __invoke(Title $title): Response
    {
        // $title->load([
        //     'media' => fn ($query) => $query->where('collection_name', 'poster'),
        //     'genres',
        // ]);

        return Inertia::render('Title', [
            // 'title' => new TitleShortResource($title),
            'title' => $title,
        ]);
    }
}
