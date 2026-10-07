<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ImageColumn::make('images')
                    ->label('')
                    ->disk('public')
                    ->limit(1)
                    ->imageHeight(56),
                TextColumn::make('name')
                    ->searchable(['name', 'sku'])
                    ->sortable()
                    // Price under the name, so phones see it without the price columns.
                    ->description(fn ($record) => inr($record->finalPrice()).($record->onSale() ? ' (sale)' : '').($record->sku ? ' · '.$record->sku : ''))
                    ->wrap(),
                TextColumn::make('category.name')
                    ->sortable()
                    ->visibleFrom('md'),
                TextColumn::make('price')
                    ->label('MRP')
                    ->money('INR', locale: 'en_IN', decimalPlaces: 0)
                    ->sortable()
                    ->visibleFrom('lg'),
                TextColumn::make('sale_price')
                    ->money('INR', locale: 'en_IN', decimalPlaces: 0)
                    ->placeholder('—')
                    ->color('danger')
                    ->sortable()
                    ->visibleFrom('lg'),
                TextInputColumn::make('stock')
                    ->type('number')
                    ->rules(['required', 'integer', 'min:0'])
                    ->sortable()
                    ->width('7rem'),
                ToggleColumn::make('is_active')
                    ->label('Visible')
                    ->visibleFrom('sm'),
                ToggleColumn::make('is_featured')
                    ->label('Featured')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')
                    ->relationship('category', 'name'),
                TernaryFilter::make('is_active')
                    ->label('Visible'),
                Filter::make('out_of_stock')
                    ->label('Out of stock')
                    ->query(fn (Builder $query) => $query->where('stock', 0)),
                Filter::make('on_sale')
                    ->label('On sale')
                    ->query(fn (Builder $query) => $query->whereNotNull('sale_price')),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
