<?php

use App\Http\Controllers\ProfileController;
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

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('/dashboard', function () {
    return Inertia::render('Welcome');
})
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
