<?php

namespace App\Filament\Resources\TitleResource\Pages;

use App\Filament\Resources\TitleResource;
use App\Models\Title;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTitle extends EditRecord
{
    protected static string $resource = TitleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $relatedIds = $this->record->related()->pluck('id')->toArray();
        $data['related'] = array_map('intval', $data['related']);

        $removedIds = array_diff($relatedIds, $data['related']);
        $addedIds = array_diff($data['related'], $relatedIds);

        if (filled($removedIds) || filled($addedIds)) {
            Title::query()
                ->where('group_id', $this->record->group_id)
                ->update(['group_id' => null]);

            $groupId = Title::max('group_id') ?? 0;

            Title::query()
                ->whereIn('id', $data['related'])
                ->update(['group_id' => $groupId + 1]);
        }

        unset($data['related']);

        return $data;
    }
}
