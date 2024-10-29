<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class KodikService
{
    public function getVideoLinks(string $link, string $userIp): array
    {
        $apiKey = env('KODIK_API_PUBLIC_KEY');
        $privateKey = env('KODIK_API_PRIVATE_KEY');

        $deadline = now('UTC')->addHours(6)->format('YmdH');
        $signatureString = "{$link}:{$userIp}:{$deadline}";
        $signature = hash_hmac('sha256', $signatureString, $privateKey);

        $response = Http::get('https://kodik.biz/api/video-links', [
            'link' => $link,
            'p' => $apiKey,
            'ip' => $userIp,
            'd' => $deadline,
            's' => $signature,
        ]);

        return $this->prepareVideoLinks(
            $response->json('links')
        );
    }

    private function prepareVideoLinks(array $links): array
    {
        $result = [];
        $resolutions = [360, 480, 720, 1080];

        foreach ($resolutions as $resolution) {
            $result[$resolution] = $links[$resolution]['Src'] ?? null;
        }

        // If 1080 is missing, use 720 as fallback
        if (! $result[1080] && $result[720]) {
            $result[1080] = $result[720];
        }

        // Remove null values for missing resolutions
        return array_filter($result);
    }
}
