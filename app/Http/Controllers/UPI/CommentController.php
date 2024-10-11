<?php

namespace App\Http\Controllers\UPI;

use App\Enums\CommentReaction as CommentReactionEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\CommentReaction as CommentReactionModel;
use App\Models\Title;
use App\Services\CommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Title $title, Request $request, CommentService $commentService): JsonResponse
    {
        $sorting = $request->sorting;
        $result = $commentService->get(
            title: $title,
            sorting: [
                'option' => $sorting['option'] ?? 'latest',
                'dir' => $sorting['dir'] ?? 'desc',
            ],
            limit: 10
        );

        return response()->json([
            'items' => CommentResource::collection($result->items()),
            'has_more' => $result->hasMorePages(),
        ]);
    }

    public function store(Title $title, Request $request, CommentService $commentService): JsonResponse
    {
        $data = $request->validate([
            // TODO: calc max without tags
            'body' => ['required', 'string', 'min:10', 'max:5000'],
            'parent_id' => ['nullable', 'numeric', 'exists:comments,id'],
        ]);

        $comment = $commentService->store($title, $data);

        return response()->json([
            'comment' => new CommentResource($comment),
        ]);
    }

    public function delete(Comment $comment, CommentService $commentService): JsonResponse
    {
        abort_unless($comment->user_id == auth()->id(), 403);

        $commentService->delete($comment);

        return response()->json('ok');
    }

    public function toggleReaction(Comment $comment, int $reaction, CommentService $commentService): JsonResponse
    {
        $reaction = CommentReactionEnum::from($reaction);
        $commentService->toggleReaction($comment, auth()->id(), $reaction);

        $reactions = $comment
            ->load([
                'reactions' => function ($query) {
                    $query->selectRaw('reaction, count(*) as count, comment_id')
                        ->groupBy('comment_id', 'reaction');
                },
            ])
            ->reactions
            ->mapWithKeys(fn (CommentReactionModel $item) => [
                $item->reaction->getName() => $item->count,
            ]);

        return response()->json([
            'reactions' => $reactions,
        ]);
    }
}
