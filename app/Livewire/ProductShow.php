<?php

namespace App\Livewire;

use App\Models\Product;
use App\Support\Cart;
use Livewire\Component;

class ProductShow extends Component
{
    public Product $product;

    public ?string $size = null;

    public int $quantity = 1;

    public function mount(Product $product): void
    {
        abort_unless($product->isVisible(), 404);

        $this->product = $product;

        // Auto-select when there is only one size (e.g. "Free Size").
        if (count($product->sizes ?? []) === 1) {
            $this->size = $product->sizes[0];
        }
    }

    public function increment(): void
    {
        $this->quantity = min($this->quantity + 1, max($this->product->stock, 1));
    }

    public function decrement(): void
    {
        $this->quantity = max($this->quantity - 1, 1);
    }

    public function addToCart(bool $buyNow = false)
    {
        $cart = app(Cart::class);
        $this->product->refresh();

        if (! $this->product->inStock()) {
            $this->addError('size', 'Sorry, this product just sold out.');

            return;
        }

        if (! empty($this->product->sizes) && ! in_array($this->size, $this->product->sizes, true)) {
            $this->addError('size', 'Please choose a size.');
            $this->dispatch('size-required');

            return;
        }

        $this->resetErrorBag('size');
        $cart->add($this->product, $this->size, max(1, $this->quantity));
        $this->dispatch('cart-updated');

        if ($buyNow) {
            return $this->redirectRoute('checkout', navigate: true);
        }

        $this->quantity = 1;
        $this->dispatch('open-cart');
    }

    public function selectSize(string $size): void
    {
        if (in_array($size, $this->product->sizes ?? [], true)) {
            $this->size = $size;
            $this->resetErrorBag('size');
        }
    }

    public function render()
    {
        $related = Product::active()
            ->where('category_id', $this->product->category_id)
            ->whereKeyNot($this->product->id)
            ->latest()
            ->limit(4)
            ->get();

        return view('livewire.product-show', ['related' => $related])
            ->title($this->product->name);
    }
}
