<?php

namespace App\Console\Commands;

use App\Models\Title;
use App\Services\ShikimoriService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ShikimoriLoadRelatedTitles extends Command
{
    protected $signature = 'app:shikimori-load-related-titles';

    protected $description = 'Load related titles from Shikimori and group them by IDs';

    protected $skip;

    protected $maxGroupId;

    public function __construct()
    {
        parent::__construct();

        $this->skip = collect();
        $this->maxGroupId = Title::max('group_id') ?? 0;
    }

    public function handle(ShikimoriService $shikimoriService)
    {
        set_time_limit(0);
        ini_set('max_execution_time', 0);
        ignore_user_abort(true);

        $titles = Title::query()
            ->whereNull('group_id')
            ->get(['id', 'group_id', 'shikimori_id']);

        $bar = $this->output->createProgressBar($titles->count());

        $bar->start();

        foreach ($titles as $title) {
            $bar->advance();

            if ($this->shouldSkipTitle($title)) {
                continue;
            }

            $relatedIds = $this->getRelatedIds($title->shikimori_id, $shikimoriService);
            if (empty($relatedIds)) {
                $this->skip->push($title->shikimori_id);

                continue;
            }

            $this->skip = $this->skip->merge($relatedIds);
            $this->updateGroupIds($relatedIds);
        }

        $bar->finish();
    }

    private function shouldSkipTitle($title)
    {
        return $this->skip->contains($title->shikimori_id);
    }

    private function getRelatedIds(int $shikimoriId, ShikimoriService $shikimoriService)
    {
        try {
            $items = $shikimoriService->getFranchise($shikimoriId);

            return $items->isEmpty() ? [] : $items->pluck('id')->toArray();
        } catch (\Exception $e) {
            Log::error("Failed to fetch related titles for ID {$shikimoriId}: ".$e->getMessage());

            return [];
        }
    }

    private function updateGroupIds($shikimoriIds)
    {
        $relatedTitles = Title::whereIn('shikimori_id', $shikimoriIds)->get();
        if ($relatedTitles->whereNull('group_id')->isNotEmpty()) {
            $this->maxGroupId++;
            Title::whereIn('shikimori_id', $shikimoriIds)
                ->update(['group_id' => $this->maxGroupId]);

            Log::info("Group ID {$this->maxGroupId} assigned to titles: ".implode(',', $shikimoriIds));
        }
    }
}
