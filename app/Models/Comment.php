<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title_id',
        'parent_id',
        'body',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function userReactions(): HasMany
    {
        return $this->reactions()->where('user_id', auth()->id());
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(CommentReaction::class);
    }

    public function replies(): HasMany
    {
        return $this
            ->hasMany(Comment::class, 'parent_id')
            ->with([
                'repliesLimited',
                'userReactions',
                'author.media' => fn ($query) => $query->where('collection_name', 'avatar'),
                'reactions' => function ($query) {
                    $query->selectRaw('reaction, count(*) as count, comment_id')
                        ->groupBy('comment_id', 'reaction');
                },
            ])
            ->take(config('comments.replies_per_page'));
    }

    public function repliesLimited(): HasMany
    {
        return $this->replies()->take(2);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(CommentReport::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function purge(): void
    {
        $this->replies->each->purge();

        $this->reports()->delete();
        $this->reactions()->delete();
        $this->delete();
    }
}
