<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;

/**
 * Session-backed shopping cart. Lines are keyed by "{product_id}:{size}".
 */
class Cart
{
    private const SESSION_KEY = 'cart';

    /**
     * @return array<string, array{product_id: int, size: ?string, quantity: int}>
     */
    private function raw(): array
    {
        return session(self::SESSION_KEY, []);
    }

    private function save(array $lines): void
    {
        session([self::SESSION_KEY => $lines]);
    }

    public static function key(int $productId, ?string $size): string
    {
        return $productId.':'.($size ?? '');
    }

    public function add(Product $product, ?string $size, int $quantity = 1): void
    {
        $lines = $this->raw();
        $key = self::key($product->id, $size);
        $current = $lines[$key]['quantity'] ?? 0;

        $lines[$key] = [
            'product_id' => $product->id,
            'size' => $size,
            'quantity' => min($current + $quantity, max($product->stock, 1)),
        ];

        $this->save($lines);
    }

    public function update(string $key, int $quantity): void
    {
        $lines = $this->raw();

        if (! isset($lines[$key])) {
            return;
        }

        if ($quantity < 1) {
            unset($lines[$key]);
        } else {
            $lines[$key]['quantity'] = $quantity;
        }

        $this->save($lines);
    }

    public function remove(string $key): void
    {
        $this->update($key, 0);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    /**
     * Cart lines with their products. Lines whose product was removed or
     * deactivated are dropped, and quantities are capped at current stock.
     *
     * @return Collection<string, object{key: string, product: Product, size: ?string, quantity: int, price: float, total: float}>
     */
    public function items(): Collection
    {
        $lines = $this->raw();

        if ($lines === []) {
            return collect();
        }

        $products = Product::active()
            ->whereIn('id', array_column($lines, 'product_id'))
            ->get()
            ->keyBy('id');

        return collect($lines)
            ->filter(fn (array $line) => $products->has($line['product_id']))
            ->map(function (array $line, string $key) use ($products) {
                $product = $products[$line['product_id']];
                $quantity = min($line['quantity'], max($product->stock, 0));

                return (object) [
                    'key' => $key,
                    'product' => $product,
                    'size' => $line['size'],
                    'quantity' => $quantity,
                    // What the customer asked for, before capping at current stock.
                    'requested' => (int) $line['quantity'],
                    'price' => $product->finalPrice(),
                    'total' => $product->finalPrice() * $quantity,
                ];
            })
            // Sold-out lines stay in the list (quantity 0) so checkout can say what ran out.
            ->filter(fn (object $item) => $item->quantity > 0 || $item->requested > 0);
    }

    public function count(): int
    {
        return array_sum(array_column($this->raw(), 'quantity'));
    }

    public function subtotal(?Collection $items = null): float
    {
        return ($items ?? $this->items())->sum('total');
    }

    public function shipping(float $subtotal): float
    {
        if ($subtotal <= 0 || $subtotal >= config('shop.free_shipping_over')) {
            return 0;
        }

        return config('shop.shipping_fee');
    }
}
