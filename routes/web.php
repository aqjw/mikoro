<?php

use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TitleController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::prefix('catalog')->as('catalog.')->group(function () {
    Route::get('genre/{genre:slug}', [CatalogController::class, 'genre'])->name('genre');
    Route::get('translation/{translation:slug}', [CatalogController::class, 'translation'])->name('translation');
    Route::get('studio/{studio:slug}', [CatalogController::class, 'studio'])->name('studio');
    Route::get('country/{country:slug}', [CatalogController::class, 'country'])->name('country');
    Route::get('year/{year}', [CatalogController::class, 'year'])->name('year');
});

Route::get('title/{title:slug}', TitleController::class)->name('title');

Route::middleware('auth')->group(function () {
    Route::get('bookmarks/{bookmark?}', BookmarkController::class)->name('bookmarks');
    Route::get('settings', SettingsController::class)->name('settings');
    Route::get('profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
});

Route::get('profile/{user:slug?}', [ProfileController::class, 'profile'])->name('profile');

require __DIR__ . '/upi.php';
require __DIR__ . '/auth.php';
