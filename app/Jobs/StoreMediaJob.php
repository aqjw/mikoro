<?php

namespace App\Jobs;

use App\Models\Title;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class StoreMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(
        protected int $titleId,
        protected array $data
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $title = Title::find($this->titleId);

        if (filled($this->data['poster'])) {
            $title->addMediaFromUrl($this->data['poster'])->toMediaCollection('poster');
        }

        foreach ($this->data['screenshots'] as $screenshotUrl) {
            $title->addMediaFromUrl($screenshotUrl)->toMediaCollection('screenshots');
        }
    }
}
