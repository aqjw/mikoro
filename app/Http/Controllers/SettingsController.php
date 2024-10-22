<?php

namespace App\Http\Controllers;

use App\Enums\QualityOption;
use App\Enums\VisibilityOption;
use App\Models\Translation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class SettingsController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $translations = Translation::query()
            ->select('translations.id', 'translations.title', DB::raw('COUNT(*) as titles_count'))
            ->leftJoin('title_translation', 'translations.id', '=', 'title_translation.translation_id')
            ->groupBy('translations.id')
            ->having('titles_count', '>', 60)
            ->orderByDesc('titles_count')
            ->get();

        return Inertia::render('Settings/Index', [
            'settings' => $request->user()->settings,
            'translations' => $translations,
            'qualities' => QualityOption::getCases(),
            'visibilities' => VisibilityOption::getCases(),
        ]);
    }
}
