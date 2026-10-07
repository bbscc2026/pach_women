<?php

namespace App\Livewire;

use App\Support\Cart;
use Livewire\Attributes\On;
use Livewire\Component;

/**
 * Slide-out mini cart. Opens on the browser "open-cart" event (Alpine) and
 * re-renders whenever the cart changes.
 */
class CartDrawer extends Component
{
    #[On('cart-updated')]
    public function refresh(): void
    {
        //
    }

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
        $threshold = (float) config('shop.free_shipping_over');

        return view('livewire.cart-drawer', [
            'items' => $items,
            'subtotal' => $subtotal,
            'freeShippingGap' => max(0, $threshold - $subtotal),
            'freeShippingProgress' => $threshold > 0 ? min(100, (int) round($subtotal / $threshold * 100)) : 100,
        ]);
    }
}
