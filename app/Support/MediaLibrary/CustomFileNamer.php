<?php

namespace App\Support\MediaLibrary;

use Illuminate\Support\Str;
use Spatie\MediaLibrary\Conversions\Conversion;
use Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer;

class CustomFileNamer extends DefaultFileNamer
{
    public function originalFileName(string $fileName): string
    {
        return Str::random(5);
    }

    public function conversionFileName(string $fileName, Conversion $conversion): string
    {
        return $conversion->getName();
    }

    public function responsiveFileName(string $fileName): string
    {
        return Str::random(5);
    }
}
