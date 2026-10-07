<?php

namespace App\Livewire;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Livewire\Component;

class Home extends Component
{
    public function render()
    {
        return view('livewire.home', [
            'banners' => Banner::active()->get(),
            'categories' => Category::active()->withCount(['products' => fn ($q) => $q->active()])->get(),
            'newArrivals' => Product::active()->latest()->limit(8)->get(),
            'onSale' => Product::active()->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price')->latest()->limit(4)->get(),
        ]);
    }
}
