<?php

namespace App\Jobs;

use App\Models\Episode;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class StoreEpisodeMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $episodeId,
        protected array $screenshots
    ) {
        $this->queue = 'episode-media';
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $episode = Episode::find($this->episodeId);

        foreach ($this->screenshots as $screenshotUrl) {
            $finalUrl = $this->getFinalUrl($screenshotUrl);

            $episode->addMediaFromUrl($finalUrl)->toMediaCollection('screenshots');
        }
    }

    /**
     * Function to get the final URL after redirection.
     */
    protected function getFinalUrl(string $url): string
    {
        $headers = get_headers($url, 1);

        if (isset($headers['Location'])) {
            $url = is_array($headers['Location']) ? end($headers['Location']) : $headers['Location'];
        }

        if (strpos($url, '//') === 0) {
            $url = 'https:'.$url;
        }

        return $url;
    }
}
