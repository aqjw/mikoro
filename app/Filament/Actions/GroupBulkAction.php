<?php

namespace App\Filament\Actions;

use App\Models\Title;
use Filament\Notifications\Notification;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\MaxWidth;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Support\Collection;

class GroupBulkAction extends BulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'group';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->icon('heroicon-o-rectangle-group')
            ->modalAlignment(Alignment::Center)
            ->modalFooterActionsAlignment(Alignment::Center)
            ->modalWidth(MaxWidth::Medium)
            ->requiresConfirmation();

        $this->action(function (Collection $records) {
            $groupId = Title::max('group_id') ?? 0;

            Title::query()
                ->whereIn('id', $records->pluck('id'))
                ->update(['group_id' => $groupId + 1]);

            Notification::make()
                ->success()
                ->title('Grouped')
                ->send();
        });
    }
}
