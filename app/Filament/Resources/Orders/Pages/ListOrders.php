<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Closure;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    // Orders are created by customers on the website, so there is no "New order" button.
    protected function getHeaderActions(): array
    {
        return [];
    }

    /**
     * Quick filters along the top — the main way to work through orders on a phone.
     */
    public function getTabs(): array
    {
        $count = fn (Closure $scope) => (string) $scope(Order::query())->count();

        // Filament injects the query by parameter name, so it must be called $query.
        $toShip = fn (Builder $query) => $query->where('status', 'confirmed');
        $shipped = fn (Builder $query) => $query->where('status', 'shipped');
        $unpaid = fn (Builder $query) => $query->where('payment_method', 'razorpay')->where('payment_status', '!=', 'paid')->where('status', 'pending');

        return [
            'to_ship' => Tab::make('To ship')->modifyQueryUsing($toShip)->badge($count($toShip))->badgeColor('warning'),
            'shipped' => Tab::make('Shipped')->modifyQueryUsing($shipped)->badge($count($shipped)),
            'all' => Tab::make('All'),
            'unpaid' => Tab::make('Unpaid online')->modifyQueryUsing($unpaid),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'to_ship';
    }
}
