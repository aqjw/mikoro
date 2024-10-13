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
        $title->load([
            'media',
            'genres',
            'studios',
            'countries',
            'related',
        ]);

        return Inertia::render('Title', [
            'title' => new TitleFullResource($title),
        ]);
    }
}
