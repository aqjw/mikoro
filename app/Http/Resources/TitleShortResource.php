<?php

namespace App\Http\Resources;

use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TitleShortResource extends JsonResource
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
            'status' => $this->status,
            'year' => $this->year,
            'last_episode' => $this->last_episode,
            'shikimori_rating' => $this->shikimori_rating,
            'poster' => filled($this->media) ? MediaService::getImageDetails($this->media, true) : null,
        ];
    }
}
