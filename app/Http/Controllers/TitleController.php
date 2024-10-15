<?php

namespace App\Http\Controllers;

use App\Http\Resources\TitleFullResource;
use App\Models\Title;
use Inertia\Inertia;
use Inertia\Response;

class TitleController extends Controller
{
    public function __invoke(Title $title): Response
    {
        $title
            ->loadCount('ratings')
            ->load([
                'media',
                'genres',
                'studios',
                'countries',
                'related',
                'episodes' => fn ($query) => $query->limit(1)->with('media'),
            ]);

        return Inertia::render('Title/Index', [
            'title' => new TitleFullResource($title),
        ]);
    }
}
