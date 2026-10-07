<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Group::make()
                    ->columnSpan(['lg' => 2])
                    ->schema([
                        Section::make('Items')
                            ->schema([
                                Repeater::make('items')
                                    ->hiddenLabel()
                                    ->relationship()
                                    ->disabled()
                                    ->addable(false)
                                    ->deletable(false)
                                    ->reorderable(false)
                                    ->columns(4)
                                    ->schema([
                                        TextInput::make('name')->columnSpan(2),
                                        TextInput::make('size')->placeholder('—'),
                                        TextInput::make('quantity')->label('Qty'),
                                        TextInput::make('price')->prefix('₹'),
                                    ]),
                                Group::make()
                                    ->columns(3)
                                    ->schema([
                                        TextEntry::make('subtotal')->money('INR', locale: 'en_IN'),
                                        TextEntry::make('shipping')->money('INR', locale: 'en_IN'),
                                        TextEntry::make('total')->money('INR', locale: 'en_IN')->weight('bold'),
                                    ]),
                            ]),

                        Section::make('Customer & delivery address')
                            ->columns(2)
                            ->schema([
                                TextInput::make('name')->required(),
                                TextInput::make('phone')->tel()->required(),
                                TextInput::make('email')->email()->required()->columnSpanFull(),
                                TextInput::make('line1')->label('Address line 1')->required()->columnSpanFull(),
                                TextInput::make('line2')->label('Address line 2')->columnSpanFull(),
                                TextInput::make('city')->required(),
                                TextInput::make('pincode')->label('PIN code')->required(),
                                Select::make('state')
                                    ->options(array_combine(config('shop.states'), config('shop.states')))
                                    ->searchable()
                                    ->required(),
                                Textarea::make('notes')->label('Customer notes')->disabled()->columnSpanFull(),
                            ]),
                    ]),

                Group::make()
                    ->columnSpan(['lg' => 1])
                    ->schema([
                        Section::make('Order')
                            ->schema([
                                TextEntry::make('number'),
                                TextEntry::make('created_at')->label('Placed on')->dateTime('d M Y, h:i A'),
                                Select::make('status')
                                    ->options(Order::STATUSES)
                                    ->required()
                                    ->helperText('Cancelling puts the stock back.'),
                                TextInput::make('tracking_number')
                                    ->helperText('Shown to the customer in My orders.'),
                            ]),

                        Section::make('Payment')
                            ->schema([
                                TextEntry::make('payment_method')
                                    ->formatStateUsing(fn (string $state) => Order::PAYMENT_METHODS[$state] ?? $state),
                                Select::make('payment_status')
                                    ->options(Order::PAYMENT_STATUSES)
                                    ->required(),
                                TextEntry::make('razorpay_payment_id')
                                    ->label('Razorpay payment ID')
                                    ->placeholder('—')
                                    ->copyable(),
                            ]),
                    ]),
            ]);
    }
}
