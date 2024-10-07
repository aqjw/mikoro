<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TitleController;
use App\Http\Controllers\UPI;
use App\Notifications\NewEpisode;
use App\Services\KodikService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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
Route::get('genre/{genre:slug}', [CatalogController::class, 'genre'])->name('catalog.genre');
Route::get('translation/{translation:slug}', [CatalogController::class, 'translation'])->name('catalog.translation');
Route::get('studio/{studio:slug}', [CatalogController::class, 'studio'])->name('catalog.studio');
Route::get('year/{year}', [CatalogController::class, 'year'])->name('catalog.year');
Route::get('status/{status}', [CatalogController::class, 'status'])->name('catalog.status');

Route::get('title/{title:slug}', TitleController::class)->name('title');

Route::group(['prefix' => 'upi', 'as' => 'upi.'], function () {
    Route::group(['prefix' => 'search', 'as' => 'search.'], function () {
        Route::get('random', [UPI\SearchController::class, 'random'])->name('random');
        Route::get('q/{type}/{query}', [UPI\SearchController::class, 'query'])->name('query');
    });

    Route::group(['prefix' => 'title', 'as' => 'title.'], function () {
        Route::get('catalog', [UPI\TitleController::class, 'catalog'])->name('catalog');
        Route::get('filters', [UPI\TitleController::class, 'filters'])->name('filters');
        Route::get('episodes/{title:id}', [UPI\TitleController::class, 'episodes'])->name('episodes');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
