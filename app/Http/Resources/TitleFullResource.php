<?php

namespace App\Http\Resources;

use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TitleFullResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'title_orig' => $this->title_orig,
            'other_title' => $this->other_title,
            'description' => $this->description,
            'status' => strtolower($this->status->name),
            'duration' => $this->duration,
            'year' => $this->year,
            'minimal_age' => $this->minimal_age,
            'last_episode' => $this->last_episode,
            'episodes_count' => $this->episodes_count,
            'shikimori_rating' => $this->shikimori_rating,
            'genres' => $this->genres->select('slug', 'name'),
            'studios' => $this->studios->select('slug', 'name'),
            'countries' => $this->countries->select('slug', 'name'),
            'related' => $this->related->select([
                'id',
                'slug',
                'title',
                'year',
                'aired_at',
                'shikimori_rating',
            ]),
            'poster' => MediaService::getImageDetails($this->getMedia('poster'), true),
            'screenshots' => MediaService::getImageDetails($this->getMedia('screenshots')),
        ];
    }
}
