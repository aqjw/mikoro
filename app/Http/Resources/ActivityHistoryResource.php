<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityHistoryResource extends JsonResource
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
            'context' => $this->context,
            'type' => $this->type->getName(),
            'title' => [
                'slug' => $this->title_slug,
                'title' => $this->title_title,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
