<?php

use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TitleController;
use App\Http\Controllers\UPI;
use App\Models\Title;
use Illuminate\Support\Facades\Route;

// Route::get('/sort', function () {
//     Title::whereNotNull('group_id')
//         // ->whereNull('group_sort')
//         ->get()
//         ->groupBy('group_id')
//         ->filter(fn ($group) => $group->count() > 1)
//         ->each(function ($group) {
//             $index = 1;
//             foreach ($group->reverse() as $title) {
//                 $title->update(['group_sort' => $index]);
//                 $index += 1;
//             }
//         })
//         ->dd();
// });

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
});

Route::prefix('upi')->as('upi.')->group(function () {
    Route::prefix('search')->as('search.')->group(function () {
        Route::get('random', [UPI\SearchController::class, 'random'])->name('random');
        Route::get('q/{type}/{query}', [UPI\SearchController::class, 'query'])->name('query');
    });

    Route::prefix('notifications')->as('notifications.')->group(function () {
        Route::get('', [UPI\NotificationController::class, 'index'])->name('get');
        Route::post('read/{notification:id}', [UPI\NotificationController::class, 'read'])->name('read');
        Route::post('read-all', [UPI\NotificationController::class, 'readAll'])->name('read_all');
    });

    Route::prefix('title')->as('title.')->group(function () {
        Route::get('catalog', [UPI\TitleController::class, 'catalog'])->name('catalog');
        Route::get('filters', [UPI\TitleController::class, 'filters'])->name('filters');
        Route::get('genres', [UPI\TitleController::class, 'genres'])->name('genres');
        Route::get('episodes/{title:id}', [UPI\TitleController::class, 'episodes'])->name('episodes');

        Route::middleware('auth')->group(function () {
            Route::post('rating/{title:id}', [UPI\TitleController::class, 'rating'])->middleware('throttle:rating')->name('rating');
            Route::post('playback-state/{title:id}', [UPI\TitleController::class, 'playbackState'])->name('playback_state');
            Route::post('episode-subscriptions/{title:id}', [UPI\TitleController::class, 'episodeSubscriptionToggle'])->name('episode_subscription_toggle');
        });

        Route::prefix('comments')->as('comments.')->group(function () {
            Route::get('{title:id}', [UPI\CommentController::class, 'index'])->name('get');
            Route::get('replies/{comment:id}/{last}', [UPI\CommentController::class, 'replies'])->name('replies');
            Route::middleware(['auth', 'throttle:comments'])->group(function () {
                Route::post('{title:id}', [UPI\CommentController::class, 'store'])->name('store');
                Route::patch('{comment:id}', [UPI\CommentController::class, 'update'])->name('update');
                Route::delete('{comment:id}', [UPI\CommentController::class, 'delete'])->name('delete');
                Route::post('reaction/{comment:id}/{reaction}', [UPI\CommentController::class, 'toggleReaction'])->name('toggle_reaction');
                Route::post('report/{comment:id}/{reason}', [UPI\CommentController::class, 'report'])->name('report');
            });
        });
    });

    Route::prefix('bookmarks')->as('bookmarks.')->middleware('auth')->group(function () {
        Route::get('get/{bookmark}', [UPI\BookmarkController::class, 'index'])->name('get');
        Route::post('{title:id}', [UPI\BookmarkController::class, 'toggle'])->name('toggle');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
