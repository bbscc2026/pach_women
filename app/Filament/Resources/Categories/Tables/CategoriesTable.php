<?php

namespace App\Filament\Resources\Categories\Tables;

use App\Models\Category;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Support\Collection;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->extraAttributes(['class' => 'pw-categories-table'])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                Split::make([
                    ImageColumn::make('image')
                        ->disk('public')
                        ->imageHeight(64)
                        ->grow(false),
                    Stack::make([
                        TextColumn::make('name')
                            ->searchable()
                            ->sortable()
                            ->wrap(),
                        TextColumn::make('products_count')
                            ->counts('products')
                            ->label('Products')
                            ->color('gray')
                            ->size('sm')
                            ->formatStateUsing(fn ($state) => $state.' products'),
                    ])->space(1),
                    ToggleColumn::make('is_active')
                        ->label('Visible')
                        ->grow(false),
                ])->extraAttributes(['class' => 'pw-category-row']),
            ])
            ->recordActions([
                EditAction::make()->label('Edit')->button(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->before(function (DeleteBulkAction $action, Collection $records) {
                            $inUse = $records->filter(fn (Category $category) => $category->products()->exists());

                            if ($inUse->isNotEmpty()) {
                                Notification::make()
                                    ->danger()
                                    ->title('Some categories still have products')
                                    ->body($inUse->pluck('name')->implode(', ').' — move their products first, or hide them instead.')
                                    ->send();

                                $action->cancel();
                            }
                        }),
                ]),
            ]);
    }
}
