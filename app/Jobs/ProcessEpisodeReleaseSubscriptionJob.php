<?php

namespace App\Jobs;

use App\Models\Episode;
use App\Notifications\NewEpisode;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class ProcessEpisodeReleaseSubscriptionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $episodeId
    ) {
        $this->queue = 'release-subscription';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $episode = Episode::find($this->episodeId);
        $title = $episode->title;

        $users = $title
            ->episodeReleaseSubscriptions()
            ->where('translation_id', $episode->translation_id)
            ->with('user')
            ->get(['user_id'])
            ->pluck('user');

        Notification::sendNow($users, new NewEpisode($title, $episode));
    }
}
