<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Spatie\MediaLibrary\ResponsiveImages\ResponsiveImage;

class MediaService
{
    /**
     * Get image details
     *
     * @param  mixed  $media  Media items or a single item
     * @param  bool  $first  Return only the first item's details
     * @param  string  $responsiveName  Name for responsive image conversion
     * @return array|null Array of image details or null
     */
    public static function getImageDetails($media, bool $first = false, string $responsiveName = 'conversion'): ?array
    {
        if (! $media) {
            return null;
        }

        if (self::isCollectionOrArray($media)) {
            return self::processMultipleMedia($media, $first);
        }

        return self::processSingleMedia($media, $responsiveName);
    }

    /**
     * Check if the given media is a collection or an array
     *
     * @param  mixed  $media
     */
    private static function isCollectionOrArray($media): bool
    {
        return is_array($media) || $media instanceof Collection;
    }

    /**
     * Process multiple media items
     *
     * @param  mixed  $media
     */
    private static function processMultipleMedia($media, bool $first): ?array
    {
        $list = collect($media)->map(fn ($item) => self::getImageDetails($item));

        return $first ? $list->first() : $list->toArray();
    }

    /**
     * Process a single media item
     *
     * @param  mixed  $media
     */
    private static function processSingleMedia($media, string $responsiveName): array
    {
        $storageUrl = self::getStorageUrl($media);

        // $responsiveImages = self::getResponsiveImages($media, $responsiveName, $storageUrl);

        return [
            'original' => $media->file_name,
            'placeholder' => self::getPlaceholderUrl($media, $storageUrl),
            // 'responsive' => $responsiveImages,
            'storage_url' => $storageUrl,
        ];
    }

    /**
     * Get storage URL from media
     *
     * @param  mixed  $media
     */
    private static function getStorageUrl($media): string
    {
        return str_replace($media->file_name, '', $media->original_url);
    }

    /**
     * Get responsive images from media
     *
     * @param  mixed  $media
     */
    private static function getResponsiveImages($media, string $responsiveName, string $storageUrl): array
    {
        $responsive = $media->responsiveImages($responsiveName);

        return $responsive->files->mapWithKeys(fn (ResponsiveImage $image) => [
            "{$image->width()}w" => str_replace([$storageUrl, 'responsive-images/'], '', $image->url()),
        ])->toArray();
    }

    /**
     * Get placeholder URL from media
     *
     * @param  mixed  $media
     */
    private static function getPlaceholderUrl($media, string $storageUrl): string
    {
        return str_replace([$storageUrl, 'conversions/'], '', $media->getUrl('placeholder'));
    }
}
