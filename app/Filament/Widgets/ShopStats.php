<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class ShopStats extends StatsOverviewWidget
{
    protected static ?int $sort = -1;

    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        // Revenue counts confirmed-or-later orders that were not cancelled.
        $sales = Order::whereNotIn('status', ['pending', 'cancelled']);

        return [
            Stat::make('Orders today', (clone $sales)->whereDate('created_at', today())->count()),
            Stat::make('Sales this month', inr((clone $sales)->where('created_at', '>=', now()->startOfMonth())->sum('total'))),
            Stat::make('Waiting to ship', Order::where('status', 'confirmed')->count())
                ->color('warning'),
            Stat::make('Low stock', Product::active()->where('stock', '<=', 2)->count())
                ->description('Visible products with 2 or fewer left')
                ->color('danger'),
        ];
    }
}
