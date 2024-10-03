<?php

namespace App\Http\Controllers;

use App\Enums\TitleStatus;
use App\Models\Genre;
use App\Models\Studio;
use App\Models\Translation;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function genre(Genre $genre): Response
    {
        return Inertia::render('Home', ['genre' => $genre->id]);
    }

    public function translation(Translation $translation): Response
    {
        return Inertia::render('Home', ['translation' => $translation->id]);
    }

    public function studio(Studio $studio): Response
    {
        return Inertia::render('Home', ['studio' => $studio->id]);
    }

    public function year(string|int $year): Response
    {
        return Inertia::render('Home', ['year' => (int) $year]);
    }

    public function status(string $status): Response
    {
        $case = TitleStatus::fromName($status);

        return Inertia::render('Home', ['status' => $case->value ?? null]);
    }
}
