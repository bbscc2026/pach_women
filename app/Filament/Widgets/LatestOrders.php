<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Orders\Tables\OrdersTable;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestOrders extends TableWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Recent orders')
            ->query(Order::query()->latest()->limit(8))
            ->paginated(false)
            ->columns([
                TextColumn::make('number')->weight('bold'),
                TextColumn::make('name')->label('Customer')->description(fn (Order $record) => $record->phone),
                TextColumn::make('total')->money('INR', locale: 'en_IN', decimalPlaces: 0),
                TextColumn::make('payment_method')->label('Payment')->badge()
                    ->formatStateUsing(fn (string $state) => $state === 'cod' ? 'COD' : 'Online')
                    ->color(fn (string $state) => $state === 'cod' ? 'gray' : 'info'),
                TextColumn::make('status')->badge()
                    ->formatStateUsing(fn (string $state) => Order::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => OrdersTable::STATUS_COLORS[$state] ?? 'gray'),
                TextColumn::make('created_at')->label('Placed')->since(),
            ])
            ->recordUrl(fn (Order $record) => OrderResource::getUrl('edit', ['record' => $record]))
            ->headerActions([
                Action::make('all')->label('All orders')->link()->url(OrderResource::getUrl()),
            ])
            ->emptyStateHeading('No orders yet')
            ->emptyStateDescription('New orders from the website will appear here.');
    }
}
