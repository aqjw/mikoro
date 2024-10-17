<?php

namespace App\Http\Controllers;

use App\Enums\BookmarkType;
use Inertia\Inertia;
use Inertia\Response;

class BookmarkController extends Controller
{
    public function __invoke(?string $bookmark = null): Response
    {
        return Inertia::render('Bookmark/Index', [
            'bookmark' => $bookmark ?? BookmarkType::Planned->getName(),
        ]);
    }
}
