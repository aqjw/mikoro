<?php

namespace App\Services;

use App\Enums\CommentReaction;
use App\Models\Comment;
use App\Models\Title;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class CommentService
{
    public const MAX_DEPTH = 3;

    public function get(Title $title, array $sorting, int $limit = 28): LengthAwarePaginator
    {
        $query = $title
            ->comments()
            ->whereNull('parent_id')
            ->with([
                'replies',
                'author.media' => fn ($query) => $query->where('collection_name', 'avatar'),
            ]);

        $this->sorting($query, $sorting);

        return $query->paginate($limit);
    }

    public function sorting($query, array $data): void
    {
        match ($data['option']) {
            'latest' => $query->latest(),
            // TODO:
            // 'rating' => $query->orderBy('shikimori_rating', $data['dir']),
            default => null
        };
    }

    public function store(Title $title, array $data): Comment
    {
        $parent_id = $data['parent_id'] ?? null;

        if ($parent_id) {
            $parentComment = Comment::find($parent_id);
            if ($parentComment) {
                $parentComment = $this->getValidParent($parentComment);
            }

            $parent_id = $parentComment->id ?? null;
        }

        return $title
            ->comments()
            ->create([
                'user_id' => auth()->id(),
                'parent_id' => $parent_id,
                'body' => strip_tags($data['body']),
            ]);
    }

    public function update(Comment $comment, string $body): Comment
    {
        $comment->update([
            'body' => $body,
        ]);

        return $comment;
    }

    public function delete(Comment $comment): void
    {
        $comment->purge();
    }

    public function getValidParent(Comment $comment): ?Comment
    {
        $depth = 0;
        $parent = $comment;

        while ($parent && $depth < self::MAX_DEPTH) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth >= self::MAX_DEPTH ? $comment->parent : $comment;
    }

    public function toggleReaction(Comment $comment, int $userId, CommentReaction $reaction): void
    {
        $existingReaction = $comment->reactions()
            ->where('user_id', $userId)
            ->where('reaction', $reaction)
            ->first();

        if ($existingReaction) {
            $existingReaction->delete();

            return;
        }

        $comment->reactions()->create([
            'user_id' => $userId,
            'reaction' => $reaction,
        ]);
    }
}
