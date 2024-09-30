<?php

namespace App\Services;

use App\Enums\TitleStatus;
use App\Enums\TitleType;
use App\Enums\TranslationType;
use App\Models\Genre;
use App\Models\Studio;
use App\Models\Title;
use App\Models\Translation;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class ShikimoriService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        // https://shikimori.one/api/animes/59765/related
    }
}
