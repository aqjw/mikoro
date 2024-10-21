<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TitleResource\Pages;
use App\Models\Title;
use App\Services\ShikimoriService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class TitleResource extends Resource
{
    protected static ?string $model = Title::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make()
                ->compact()
                ->columns(2)
                ->columnSpan(2)
                ->schema([
                    Forms\Components\TextInput::make('slug')
                        ->rules('required'),

                    Forms\Components\TextInput::make('title')
                        ->rules('required'),

                    Forms\Components\TextInput::make('title_orig')
                        ->rules('required'),

                    Forms\Components\TextInput::make('other_title')
                        ->rules('required'),

                    Forms\Components\Textarea::make('description')
                        ->columnSpanFull()
                        ->rules('required'),
                ]),

            Forms\Components\Section::make('Related')
                ->description(fn (Title $title): string => 'Group ID '.$title->group_id)
                ->compact()
                ->columnSpan(1)
                ->schema([
                    Forms\Components\CheckboxList::make('related')
                        ->hiddenLabel()
                        ->columns(1)
                        ->afterStateHydrated(function (Forms\Components\CheckboxList $component, Title $title) {
                            $component->state($title->related()->pluck('id')->toArray());
                        })
                        ->descriptions(function (Title $record, ShikimoriService $shikimoriService) {
                            return cache()
                                ->store('array')
                                ->sear('checkbox_list_descriptions', function () use ($record, $shikimoriService) {
                                    $items = $shikimoriService->getFranchise($record->shikimori_id);

                                    return Title::query()
                                        ->whereIn('shikimori_id', $items->pluck('id'))
                                        ->get(['released_at', 'id', 'duration'])
                                        ->mapWithKeys(function ($title) {
                                            $date = $title->released_at?->translatedFormat('F Y');
                                            $duration = $title->duration;

                                            return [$title->id => "{$date} - {$duration} min"];
                                        })
                                        ->toArray();
                                });
                        })
                        ->options(function (Title $record, ShikimoriService $shikimoriService) {
                            return cache()
                                ->store('array')
                                ->sear('checkbox_list_options', function () use ($record, $shikimoriService) {
                                    $items = $shikimoriService->getFranchise($record->shikimori_id);

                                    return Title::query()
                                        ->whereIn('shikimori_id', $items->pluck('id'))
                                        ->orderByDesc('group_sort')
                                        ->get(['title', 'id', 'group_id'])
                                        ->sortBy(fn ($title) => $title->group_id !== $record->group_id)
                                        ->pluck('title', 'id')
                                        ->toArray();
                                });
                        }),

                ]),
        ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            // ->defaultSort('last_episode_at', 'desc')
            ->reorderable('group_sort')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID'),

                Tables\Columns\TextColumn::make('title')
                    ->searchable(),

                Tables\Columns\TextColumn::make('duration')
                    ->suffix(' минут'),

                Tables\Columns\TextColumn::make('group_id'),

                Tables\Columns\TextColumn::make('released_at')
                    ->date('F Y')
                    ->dateTimeTooltip(),

                Tables\Columns\TextColumn::make('last_episode_at')
                    ->dateTimeTooltip()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('group_id')
                    ->label('Group ID')
                    ->searchable()
                    ->getSearchResultsUsing(function (string $search) {
                        if (filled($search)) {
                            return Title::query()
                                ->select('group_id')
                                ->where('group_id', 'like', "%{$search}%")
                                ->groupBy('group_id')
                                ->limit(50)
                                ->pluck('group_id', 'group_id')
                                ->toArray();
                        }

                        return [];
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    \App\Filament\Actions\GroupBulkAction::make(),
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTitles::route('/'),
            'create' => Pages\CreateTitle::route('/create'),
            'edit' => Pages\EditTitle::route('/{record}/edit'),
        ];
    }
}
