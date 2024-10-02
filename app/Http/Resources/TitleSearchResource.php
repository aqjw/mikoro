<?php

namespace App\Http\Resources;

use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TitleSearchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'slug' => $this->slug,
            'title' => $this->title,
            'year' => $this->year,
            'episodes' => $this->episodes_count,
            'shikimori_rating' => $this->shikimori_rating,
            'genres' => $this->genres->select(['slug', 'name']),
            'poster' => filled($this->media) ? MediaService::getImageDetails($this->media, true) : null,
        ];
    }
}
