<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Resources\Orders\OrderActions;
use App\Models\Order;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public const STATUS_COLORS = [
        'pending' => 'warning',
        'confirmed' => 'info',
        'shipped' => 'primary',
        'delivered' => 'success',
        'cancelled' => 'gray',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            // New orders show up without refreshing.
            ->poll('30s')
            ->columns([
                // On phones only number/customer, total and status are shown; the rest from tablet width.
                TextColumn::make('number')
                    ->searchable()
                    ->weight('bold')
                    ->description(fn (Order $record) => $record->name, position: 'below')
                    ->copyable(),
                TextColumn::make('name')
                    ->label('Customer')
                    ->description(fn (Order $record) => $record->phone)
                    ->searchable(['name', 'phone', 'email'])
                    ->visibleFrom('md'),
                TextColumn::make('city')
                    ->description(fn (Order $record) => $record->pincode)
                    ->toggleable()
                    ->visibleFrom('lg'),
                TextColumn::make('total')
                    ->money('INR', locale: 'en_IN', decimalPlaces: 0)
                    ->description(fn (Order $record) => $record->payment_method === 'cod' ? 'COD' : ($record->payment_status === 'paid' ? 'Paid online' : 'Online · unpaid'))
                    ->sortable(),
                TextColumn::make('payment_method')
                    ->label('Payment')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'cod' ? 'COD' : 'Online')
                    ->color(fn (string $state) => $state === 'cod' ? 'gray' : 'info')
                    ->visibleFrom('lg'),
                TextColumn::make('payment_status')
                    ->label('Paid?')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => Order::PAYMENT_STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'paid' => 'success',
                        'failed' => 'danger',
                        default => 'warning',
                    })
                    ->visibleFrom('lg'),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (string $state, Order $record) => $record->statusLabel())
                    ->color(fn (string $state) => self::STATUS_COLORS[$state] ?? 'gray'),
                TextColumn::make('created_at')
                    ->label('Date')
                    ->since()
                    ->dateTimeTooltip('d M Y, h:i A')
                    ->sortable()
                    ->visibleFrom('md'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(Order::STATUSES),
                SelectFilter::make('payment_method')
                    ->options(['cod' => 'COD', 'razorpay' => 'Online']),
                SelectFilter::make('payment_status')
                    ->options(Order::PAYMENT_STATUSES),
            ])
            ->recordActions([
                ActionGroup::make([
                    OrderActions::ship(),
                    OrderActions::deliver(),
                    OrderActions::whatsapp(),
                    OrderActions::call(),
                    EditAction::make()->label('Open order'),
                    OrderActions::cancel(),
                ]),
            ]);
    }
}
