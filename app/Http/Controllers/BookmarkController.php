<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class BookmarkController extends Controller
{
    public function __invoke(?string $type = null): Response
    {
        return Inertia::render('Bookmark/Index', [
            //
        ]);
    }
}
