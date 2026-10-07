<?php

namespace App\Livewire;

use App\Support\Cart;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Cart button with item badge. "header" is the icon in the top bar,
 * "tab" is the item in the mobile bottom navigation. Both open the cart drawer.
 */
class CartCount extends Component
{
    public string $variant = 'header';

    #[On('cart-updated')]
    public function refresh(): void
    {
        //
    }

    public function render(Cart $cart)
    {
        return view('livewire.cart-count', ['count' => $cart->count()]);
    }
}
