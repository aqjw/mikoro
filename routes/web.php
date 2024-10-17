<?php

use App\Http\Controllers\BookmarkController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TitleController;
use App\Http\Controllers\UPI;
use App\Services\ShikimoriService;
use Illuminate\Support\Facades\Route;

Route::get('/test', function (ShikimoriService $shikimoriService) {
    $id = 54857;
    $franchise = $shikimoriService->getFranchise($id);
    $related = $shikimoriService->getRelated($id);
    dd([
        'franchise' => $franchise->pluck('name', 'id')->sort()->toArray(),
        'related' => $related->pluck('russian', 'id')->sort()->toArray(),
    ]);
});

Route::get('/', HomeController::class)->name('home');
Route::get('genre/{genre:slug}', [CatalogController::class, 'genre'])->name('catalog.genre');
Route::get('translation/{translation:slug}', [CatalogController::class, 'translation'])->name('catalog.translation');
Route::get('studio/{studio:slug}', [CatalogController::class, 'studio'])->name('catalog.studio');
Route::get('country/{country:slug}', [CatalogController::class, 'country'])->name('catalog.country');
Route::get('year/{year}', [CatalogController::class, 'year'])->name('catalog.year');

Route::get('title/{title:slug}', TitleController::class)->name('title');

Route::group(['middleware' => 'auth'], function () {
    Route::get('bookmarks/{bookmark?}', BookmarkController::class)->name('bookmarks');
});

Route::group(['prefix' => 'upi', 'as' => 'upi.'], function () {
    Route::group(['prefix' => 'search', 'as' => 'search.'], function () {
        Route::get('random', [UPI\SearchController::class, 'random'])->name('random');
        Route::get('q/{type}/{query}', [UPI\SearchController::class, 'query'])->name('query');
    });

    Route::group(['prefix' => 'notifications', 'as' => 'notifications.'], function () {
        Route::get('', [UPI\NotificationController::class, 'index'])->name('get');
        Route::post('read/{notification:id}', [UPI\NotificationController::class, 'read'])->name('read');
        Route::post('read-all', [UPI\NotificationController::class, 'readAll'])->name('read_all');
    });

    Route::group(['prefix' => 'title', 'as' => 'title.'], function () {
        Route::get('catalog', [UPI\TitleController::class, 'catalog'])->name('catalog');
        Route::get('filters', [UPI\TitleController::class, 'filters'])->name('filters');

        Route::get('genres', [UPI\TitleController::class, 'genres'])->name('genres');
        Route::get('episodes/{title:id}', [UPI\TitleController::class, 'episodes'])->name('episodes');

        Route::group(['middleware' => 'auth'], function () {
            Route::post('rating/{title:id}', [UPI\TitleController::class, 'rating'])->middleware('throttle:rating')->name('rating');
            Route::post('playback-state/{title:id}', [UPI\TitleController::class, 'playbackState'])->name('playback_state');
            Route::post('episode-subscriptions/{title:id}', [UPI\TitleController::class, 'episodeSubscriptionToggle'])->name('episode_subscription_toggle');
        });

        Route::group(['prefix' => 'comments', 'as' => 'comments.'], function () {
            Route::get('{title:id}', [UPI\CommentController::class, 'index'])->name('get');
            Route::get('replies/{comment:id}/{last}', [UPI\CommentController::class, 'replies'])->name('replies');
            Route::group(['middleware' => ['auth', 'throttle:comments']], function () {
                Route::post('{title:id}', [UPI\CommentController::class, 'store'])->name('store');
                Route::patch('{comment:id}', [UPI\CommentController::class, 'update'])->name('update');
                Route::delete('{comment:id}', [UPI\CommentController::class, 'delete'])->name('delete');
                Route::post('reaction/{comment:id}/{reaction}', [UPI\CommentController::class, 'toggleReaction'])->name('toggle_reaction');
                Route::post('report/{comment:id}/{reason}', [UPI\CommentController::class, 'report'])->name('report');
            });
        });
    });

    Route::group(['prefix' => 'bookmarks', 'as' => 'bookmarks.', 'middleware' => 'auth'], function () {
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
