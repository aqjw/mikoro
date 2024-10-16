<?php

namespace App\Http\Resources;

use App\Enums\TranslationType;
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
        $relatedFields = ['id', 'slug', 'title', 'year', 'released_at', 'shikimori_rating', 'rating'];

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
            'shikimori_votes' => $this->shikimori_votes,
            'rating' => $this->rating,
            'rating_votes' => $this->ratings_count,
            'user_voted' => $this->userVoted,
            'single_episode' => $this->singleEpisode,
            'translations' => $this->translations
                ->sort(function ($a, $b) {
                    if (! $a->type->is($b->type)) {
                        return $a->type->is(TranslationType::Voice) ? -1 : 1;
                    }

                    return strcmp($a->title, $b->title);
                })
                ->select('id', 'title')
                ->values(),
            'bookmark' => $this->bookmarkType,
            'episode_subscriptions' => $this->episodeReleaseSubscriptionTranslationIds,
            'genres' => $this->genres->select('slug', 'name'),
            'studios' => $this->studios->select('slug', 'name'),
            'countries' => $this->countries->select('slug', 'name'),
            'related' => $this->related->isEmpty()
                ? [$this->only($relatedFields)]
                : $this->related->select($relatedFields),
            'poster' => MediaService::getImageDetails($this->getMedia('poster'), true),
            'screenshots' => MediaService::getImageDetails(
                $this->episodes[0]->media ?? null
            ),
        ];
    }
}
