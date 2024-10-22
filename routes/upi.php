<?php

use App\Http\Controllers\UPI\BookmarkController;
use App\Http\Controllers\UPI\CommentController;
use App\Http\Controllers\UPI\NotificationController;
use App\Http\Controllers\UPI\SearchController;
use App\Http\Controllers\UPI\SettingsController;
use App\Http\Controllers\UPI\TitleController;
use Illuminate\Support\Facades\Route;

Route::prefix('upi')->as('upi.')->group(function () {
    Route::prefix('search')->as('search.')->group(function () {
        Route::get('random', [SearchController::class, 'random'])->name('random');
        Route::get('q/{type}/{query}', [SearchController::class, 'query'])->name('query');
    });

    Route::prefix('notifications')->as('notifications.')->group(function () {
        Route::get('', [NotificationController::class, 'index'])->name('get');
        Route::post('read/{notification:id}', [NotificationController::class, 'read'])->name('read');
        Route::post('read-all', [NotificationController::class, 'readAll'])->name('read_all');
    });

    Route::prefix('title')->as('title.')->group(function () {
        Route::get('catalog', [TitleController::class, 'catalog'])->name('catalog');
        Route::get('filters', [TitleController::class, 'filters'])->name('filters');
        Route::get('genres', [TitleController::class, 'genres'])->name('genres');
        Route::get('episodes/{title:id}', [TitleController::class, 'episodes'])->name('episodes');

        Route::middleware('auth')->group(function () {
            Route::post('rating/{title:id}', [TitleController::class, 'rating'])->middleware('throttle:rating')->name('rating');
            Route::post('playback-state/{title:id}', [TitleController::class, 'playbackState'])->name('playback_state');
            Route::post('episode-subscriptions/{title:id}', [TitleController::class, 'episodeSubscriptionToggle'])->name('episode_subscription_toggle');
        });

        Route::prefix('comments')->as('comments.')->group(function () {
            Route::get('{title:id}', [CommentController::class, 'index'])->name('get');
            Route::get('replies/{comment:id}/{last}', [CommentController::class, 'replies'])->name('replies');
            Route::middleware(['auth', 'throttle:comments'])->group(function () {
                Route::post('{title:id}', [CommentController::class, 'store'])->name('store');
                Route::patch('{comment:id}', [CommentController::class, 'update'])->name('update');
                Route::delete('{comment:id}', [CommentController::class, 'delete'])->name('delete');
                Route::post('reaction/{comment:id}/{reaction}', [CommentController::class, 'toggleReaction'])->name('toggle_reaction');
                Route::post('report/{comment:id}/{reason}', [CommentController::class, 'report'])->name('report');
            });
        });
    });

    Route::prefix('bookmarks')->as('bookmarks.')->middleware('auth')->group(function () {
        Route::get('get/{bookmark}', [BookmarkController::class, 'index'])->name('get');
        Route::post('{title:id}', [BookmarkController::class, 'toggle'])->name('toggle');
    });

    Route::post('settings', SettingsController::class)->name('settings.save');
});
