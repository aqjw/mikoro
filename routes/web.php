<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UPI\SearchController;
use App\Http\Controllers\UPI\TitleController;
use App\Notifications\NewEpisode;
use App\Services\KodikService;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/kodik', function (KodikService $kodikService) {
    $kodikService->list();
});

// Route::get('/notify', function (Request $request) {
//     $titleId = 2;
//     $episodeId = 2;
//     $request->user()->notify(new NewEpisode($titleId, $episodeId));

//     // Notification::send($users, new NewEpisode($title, $episode));
// });

Route::get('/', HomeController::class)->name('home');

Route::get('/dashboard', fn () => Inertia::render('Home'))->middleware(['auth', 'verified'])->name('dashboard');

Route::group(['prefix' => 'upi', 'as' => 'upi.'], function () {
    Route::group(['prefix' => 'search', 'as' => 'search.'], function () {
        Route::get('random', [SearchController::class, 'random'])->name('random');
        Route::get('q/{type}/{query}', [SearchController::class, 'query'])->name('query');
    });

    Route::group(['prefix' => 'title', 'as' => 'title.'], function () {
        Route::get('catalog', [TitleController::class, 'catalog'])->name('catalog');
        Route::get('filters', [TitleController::class, 'filters'])->name('filters');
    });
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
