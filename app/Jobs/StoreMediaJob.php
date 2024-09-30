<?php

namespace App\Jobs;

use App\Models\Title;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StoreMediaJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    /**
     * Create a new job instance.
     */
    public function __construct(
        protected Title $title,
        protected array $data
    ) {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $media = [
            'poster' => $this->data['material_data']['poster_url'] ?? null,
            'screenshots' => $this->data['material_data']['screenshots'] ?? []
        ];

        if (filled($media['poster'])) {
            $this->title->addMediaFromUrl($media['poster'])->toMediaCollection('poster');
        }

        foreach ($media['screenshots'] as $screenshotUrl) {
            $this->title->addMediaFromUrl($screenshotUrl)->toMediaCollection('screenshots');
        }
    }
}
