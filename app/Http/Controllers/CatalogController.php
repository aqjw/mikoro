<?php

namespace App\Http\Controllers;

use App\Enums\TitleStatus;
use App\Models\Country;
use App\Models\Genre;
use App\Models\Studio;
use App\Models\Translation;
use Inertia\Inertia;
use Inertia\Response;

class CatalogController extends Controller
{
    public function genre(Genre $genre): Response
    {
        return Inertia::render('Home', [
            'filter' => [
                'key' => 'genres',
                'value' => $genre->id,
            ],
        ]);
    }

    public function translation(Translation $translation): Response
    {
        return Inertia::render('Home', [
            'filter' => [
                'key' => 'translations',
                'value' => $translation->id,
            ],
        ]);
    }

    public function studio(Studio $studio): Response
    {
        return Inertia::render('Home', [
            'filter' => [
                'key' => 'studios',
                'value' => $studio->id,
            ],
        ]);
    }

    public function country(Country $country): Response
    {
        return Inertia::render('Home', [
            'filter' => [
                'key' => 'countries',
                'value' => $country->id,
            ],
        ]);
    }

    public function year(string|int $year): Response
    {
        return Inertia::render('Home', [
            'filter' => [
                'key' => 'years',
                'value' => (int) $year,
            ],
        ]);
    }

    public function status(string $status): Response
    {
        $case = TitleStatus::fromName($status);

        return Inertia::render('Home', [
            'filter' => [
                'key' => 'statuses',
                'value' => $case->value ?? null,
            ],
        ]);
    }
}
