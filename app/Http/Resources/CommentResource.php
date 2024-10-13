<?php

namespace App\Http\Resources;

use App\Models\CommentReaction;
use App\Services\MediaService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'parent_id' => $this->parent_id,
            'body' => $this->body,
            'author' => [
                'id' => $this->user_id,
                'name' => $this->author->name,
                'avatar' => MediaService::getImageDetails($this->author->getMedia('avatar'), true),
            ],
            'userReactions' => $this->userReactions->pluck('reaction'),
            'reactions' => $this->reactions
                ->mapWithKeys(fn (CommentReaction $item) => [
                    $item->reaction->getName() => $item->count,
                ]),
            'replies' => self::collection($this->repliesLimited),
            'replies_total' => $this->replies_count,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
