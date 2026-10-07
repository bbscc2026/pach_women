<?php

namespace App\Models;

use App\Support\StoredFiles;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'category_id', 'name', 'slug', 'sku', 'description', 'fabric', 'color',
    'price', 'sale_price', 'images', 'sizes', 'stock', 'is_featured', 'is_active',
])]
class Product extends Model
{
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'images' => 'array',
            'sizes' => 'array',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * Visible on the website: the product and its category are both switched on.
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)
            ->whereHas('category', fn (Builder $q) => $q->where('is_active', true));
    }

    public function isVisible(): bool
    {
        return $this->is_active && (bool) $this->category?->is_active;
    }

    protected static function booted(): void
    {
        // Remove photo files from disk when they're replaced or the product is deleted.
        static::updated(function (Product $product) {
            if ($product->wasChanged('images')) {
                StoredFiles::delete(array_diff((array) $product->getOriginal('images'), (array) $product->images));
            }
        });

        static::deleted(fn (Product $product) => StoredFiles::delete((array) $product->images));
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function onSale(): bool
    {
        return $this->sale_price !== null && (float) $this->sale_price < (float) $this->price;
    }

    /**
     * The price the customer pays: the sale price when one is set.
     */
    public function finalPrice(): float
    {
        return $this->onSale() ? (float) $this->sale_price : (float) $this->price;
    }

    public function discountPercent(): int
    {
        return $this->onSale() ? (int) round(100 - ((float) $this->sale_price / (float) $this->price) * 100) : 0;
    }

    public function inStock(): bool
    {
        return $this->stock > 0;
    }

    /**
     * @return array<int, string>
     */
    public function imageUrls(): array
    {
        return collect($this->images ?? [])
            ->map(fn (string $path) => Storage::disk('public')->url($path))
            ->all();
    }

    public function thumbnailUrl(): ?string
    {
        return $this->imageUrls()[0] ?? null;
    }
}
