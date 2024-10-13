<?php

namespace App\Services;

use App\Enums\CommentReaction;
use App\Enums\CommentReportReason;
use App\Models\Comment;
use App\Models\Title;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class CommentService
{
    public function get(Title $title, string $sorting, int $limit = 28): LengthAwarePaginator
    {
        $query = $title
            ->comments()
            ->whereNull('parent_id')
            ->with([
                'repliesLimited',
                'userReactions',
                'author.media' => fn ($query) => $query->where('collection_name', 'avatar'),
                'reactions' => function ($query) {
                    $query->selectRaw('reaction, count(*) as count, comment_id')
                        ->groupBy('comment_id', 'reaction');
                },
            ]);

        $this->sorting($query, $sorting);
        $result = $query->paginate($limit);

        $result->setCollection(
            $this->countReplies($result->getCollection())
        );

        return $result;
    }

    public function countReplies(Collection $collection): Collection
    {
        function getIds($items)
        {
            $ids = [];
            foreach ($items as $item) {
                $ids[] = $item->id;
                if (! empty($item->repliesLimited)) {
                    $ids = array_merge($ids, getIds($item->repliesLimited));
                }
            }

            return $ids;
        }

        function populateRepliesCount($items, $repliesCount)
        {
            foreach ($items as $item) {
                if (isset($repliesCount[$item->id])) {
                    $item->replies_count = $repliesCount[$item->id];
                }

                if (! empty($item->repliesLimited)) {
                    populateRepliesCount($item->repliesLimited, $repliesCount);
                }
            }
        }

        $ids = getIds($collection);

        $repliesCount = Comment::whereIn('id', $ids)
            ->withCount('replies')
            ->pluck('replies_count', 'id');

        populateRepliesCount($collection, $repliesCount);

        return $collection;
    }

    public function sorting($query, string $sorting): void
    {
        match ($sorting) {
            'latest' => $query->latest(),
            'oldest' => $query->oldest(),
            'reactions' => $query
                ->withCount('reactions')
                ->orderByDesc('reactions_count'),
            default => null
        };
    }

    public function getReplies(Comment $comment, int $lastCommentId, int $limit = 3): Collection
    {
        $result = $comment
            ->replies()
            ->where('id', '>', $lastCommentId)
            ->take($limit)
            ->get();

        $result = $this->countReplies($result);

        return $result;
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
        $max_depth = config('comments.max_depth');

        while ($parent && $depth < $max_depth) {
            $depth++;
            $parent = $parent->parent;
        }

        return $depth >= $max_depth ? $comment->parent : $comment;
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

    public function report(Comment $comment, int $userId, CommentReportReason $reason): void
    {
        $comment
            ->reports()
            ->create([
                'user_id' => $userId,
                'reason' => $reason,

            ]);
    }
}
