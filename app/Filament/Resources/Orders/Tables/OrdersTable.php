<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Filament\Resources\Orders\OrderActions;
use App\Models\Order;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Support\Enums\Alignment;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
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
            // Card-style rows: order + customer on the left, total + status on the right.
            // Reads like a phone app list and still works as rows on desktop.
            ->columns([
                Split::make([
                    Stack::make([
                        TextColumn::make('number')
                            ->weight(FontWeight::Bold)
                            ->searchable()
                            ->copyable(),
                        TextColumn::make('name')
                            ->label('Customer')
                            ->searchable(['name', 'phone', 'email'])
                            ->description(fn (Order $record) => $record->phone.' · '.$record->city),
                    ])->space(1),

                    TextColumn::make('created_at')
                        ->label('Placed')
                        ->since()
                        ->dateTimeTooltip('d M Y, h:i A')
                        ->sortable()
                        ->color('gray')
                        ->grow(false)
                        ->visibleFrom('md'),

                    Stack::make([
                        TextColumn::make('total')
                            ->money('INR', locale: 'en_IN', decimalPlaces: 0)
                            ->weight(FontWeight::SemiBold)
                            ->sortable()
                            ->alignment(Alignment::End)
                            ->description(fn (Order $record) => $record->payment_method === 'cod' ? 'COD' : ($record->payment_status === 'paid' ? 'Paid online' : 'Online · unpaid')),
                        TextColumn::make('status')
                            ->badge()
                            ->formatStateUsing(fn (string $state, Order $record) => $record->statusLabel())
                            ->color(fn (string $state) => self::STATUS_COLORS[$state] ?? 'gray')
                            ->alignment(Alignment::End),
                    ])->space(1)->alignment(Alignment::End)->grow(false),
                ]),
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
