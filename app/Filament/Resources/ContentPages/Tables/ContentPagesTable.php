<?php

namespace App\Filament\Resources\ContentPages\Tables;

use App\Models\ContentPage;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class ContentPagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->description(fn (ContentPage $record) => '/pages/'.$record->slug),
                TextColumn::make('updated_at')
                    ->label('Last edited')
                    ->since()
                    ->sortable(),
                ToggleColumn::make('is_active')
                    ->label('Visible'),
            ])
            ->recordActions([
                Action::make('view')
                    ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                    ->url(fn (ContentPage $record) => route('page', $record), shouldOpenInNewTab: true),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
