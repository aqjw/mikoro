<?php

namespace App\Jobs;

use App\Models\Title;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class StoreTitleMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $titleId,
        protected string $posterUrl
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $title = Title::find($this->titleId);
        $title->addMediaFromUrl($this->posterUrl)->toMediaCollection('poster');
    }
}
