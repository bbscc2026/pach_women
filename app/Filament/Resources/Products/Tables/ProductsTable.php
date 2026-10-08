<?php

namespace App\Filament\Resources\Products\Tables;

use App\Models\Product;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
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
            // Card-style rows (photo, name, price, stock) that fit a phone without sideways scrolling.
            ->columns([
                Split::make([
                    ImageColumn::make('images')
                        ->label('')
                        ->disk('public')
                        ->limit(1)
                        ->imageHeight(64)
                        ->grow(false),

                    Stack::make([
                        TextColumn::make('name')
                            ->searchable(['name', 'sku'])
                            ->sortable()
                            ->weight(FontWeight::Medium)
                            ->wrap(),
                        TextColumn::make('price')
                            ->label('Price')
                            ->sortable()
                            ->formatStateUsing(fn (Product $record) => inr($record->finalPrice()).($record->onSale() ? '  (MRP '.inr($record->price).')' : ''))
                            ->color(fn (Product $record) => $record->onSale() ? 'danger' : null),
                        TextColumn::make('category.name')
                            ->color('gray')
                            ->size('xs')
                            ->formatStateUsing(fn (string $state, Product $record) => $state.($record->sku ? ' · '.$record->sku : '')),
                        // Stock sits under the name so the name gets the full width on phones.
                        TextInputColumn::make('stock')
                            ->type('number')
                            ->rules(['required', 'integer', 'min:0'])
                            ->sortable()
                            ->prefix('Stock')
                            ->extraAttributes(['style' => 'max-width: 9rem']),
                    ])->space(2),

                    ToggleColumn::make('is_active')
                        ->label('Visible')
                        ->grow(false)
                        ->visibleFrom('sm'),
                ]),
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
                EditAction::make()->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
