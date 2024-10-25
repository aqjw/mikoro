<?php

namespace App\Http\Resources;

use App\Enums\TitleType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookmarkTableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'updated_at' => $this->updated_at->toISOString(),
            'title_id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'released_at' => $this->released_at,
            'last_episode' => $this->last_episode,
            'shikimori_rating' => $this->shikimori_rating,
            'shikimori_votes' => $this->shikimori_votes,
            'single_episode' => TitleType::Anime->is($this->type),
        ];
    }
}