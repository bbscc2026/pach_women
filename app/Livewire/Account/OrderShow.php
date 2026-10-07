<?php

namespace App\Livewire\Account;

use App\Models\Order;
use Livewire\Component;

class OrderShow extends Component
{
    public Order $order;

    public function mount(Order $order): void
    {
        abort_unless($order->user_id === auth()->id(), 404);

        $this->order = $order->load('items.product');
    }

    public function render()
    {
        return view('livewire.account.order-show')->title('Order '.$this->order->number);
    }
}
