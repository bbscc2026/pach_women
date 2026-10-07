<?php

namespace App\Livewire;

use App\Support\Cart;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Your cart')]
class CartPage extends Component
{
    public function updateQuantity(string $key, int $quantity): void
    {
        app(Cart::class)->update($key, $quantity);
        $this->dispatch('cart-updated');
    }

    public function remove(string $key): void
    {
        app(Cart::class)->remove($key);
        $this->dispatch('cart-updated');
    }

    public function render(Cart $cart)
    {
        $items = $cart->items();
        $subtotal = $cart->subtotal($items);
        $shipping = $cart->shipping($subtotal);

        return view('livewire.cart-page', [
            'items' => $items,
            'subtotal' => $subtotal,
            'shipping' => $shipping,
            'total' => $subtotal + $shipping,
            'freeShippingGap' => max(0, config('shop.free_shipping_over') - $subtotal),
            'freeShippingProgress' => min(100, (int) round($subtotal / max((float) config('shop.free_shipping_over'), 1) * 100)),
        ]);
    }
}
