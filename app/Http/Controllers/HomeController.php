<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    public function __invoke(): Response
    {

        // \App\Models\Title::query()
        //     ->join('genre_title', 'titles.id', '=', 'genre_title.title_id')
        //     ->where('genre_title.genre_id', 28)
        //     ->get()
        //     ->each->purge();
        // dd('');

        // \App\Models\Title::query()
        //     ->join('country_title', 'titles.id', '=', 'country_title.title_id')
        //     ->whereIn('country_title.country_id', [2])

        //     ->where('duration', '<=', 4)
        //     ->pluck('shikimori_id', 'title')
        //     ->sortDesc()
        //     ->dd();

        // \App\Models\Title::where('duration', '<', 5)
        //     ->get()
        //     ->dd();//->each->purge();

        return Inertia::render('Home');
    }
}
