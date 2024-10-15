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
                ->compact()
                ->schema([
                    Forms\Components\CheckboxList::make('related')
                        ->hiddenLabel()
                        ->columns(3)
                        ->afterStateHydrated(function (Forms\Components\CheckboxList $component, Title $title) {
                            $component->state($title->related()->pluck('id')->toArray());
                        })
                        ->options(function (Title $record, ShikimoriService $shikimoriService) {
                            $items = $shikimoriService->getFranchise($record->shikimori_id);
                            $titles = Title::whereIn('shikimori_id', $items->pluck('id'))->pluck('title', 'id');

                            return $titles->sort()->toArray();
                        }),

                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('updated_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID'),

                Tables\Columns\TextColumn::make('title')
                    ->searchable(),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTimeTooltip()
                    ->sortable()
                    ->since(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
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
