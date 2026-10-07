<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;

class Shop extends Component
{
    private const PAGE_SIZE = 12;

    public ?Category $category = null;

    /** Products shown so far; "Load more" adds another page. */
    #[Locked]
    public int $perPage = self::PAGE_SIZE;

    #[Url(as: 'q', except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $size = '';

    #[Url(except: '')]
    public string $price = '';

    #[Url(except: 'new')]
    public string $sort = 'new';

    public const PRICE_RANGES = [
        'under-1000' => ['Under ₹1,000', 0, 999.99],
        '1000-2000' => ['₹1,000 – ₹2,000', 1000, 2000],
        'over-2000' => ['Over ₹2,000', 2000.01, null],
    ];

    public const SORTS = [
        'new' => 'Newest',
        'price-asc' => 'Price: low to high',
        'price-desc' => 'Price: high to low',
        'sale' => 'On sale',
    ];

    public const SIZES = ['XS', 'S', 'M', 'L', 'XL', 'XXL', 'Free Size'];

    public function mount(?Category $category = null): void
    {
        abort_if($category && ! $category->is_active, 404);

        $this->category = $category;
    }

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'size', 'price', 'sort'])) {
            $this->perPage = self::PAGE_SIZE;
        }
    }

    public function loadMore(): void
    {
        $this->perPage += self::PAGE_SIZE;
    }

    public function clearFilters(): void
    {
        $this->reset('search', 'size', 'price', 'sort', 'perPage');
    }

    public function activeFilterCount(): int
    {
        return count(array_filter([$this->size, $this->price, $this->search]));
    }

    public function render()
    {
        // Price filters and sorting use the effective price (sale price when set).
        $effectivePrice = 'COALESCE(CASE WHEN sale_price < price THEN sale_price END, price)';

        $products = Product::active()
            ->when($this->category, fn (Builder $q) => $q->where('category_id', $this->category->id))
            ->when($this->search !== '', fn (Builder $q) => $q->where(fn (Builder $q) => $q
                ->where('name', 'like', "%{$this->search}%")
                ->orWhere('description', 'like', "%{$this->search}%")))
            ->when($this->size !== '', fn (Builder $q) => $q->whereJsonContains('sizes', $this->size))
            ->when(isset(self::PRICE_RANGES[$this->price]), function (Builder $q) use ($effectivePrice) {
                [, $min, $max] = self::PRICE_RANGES[$this->price];
                // Bounds come from the constant above, so they are inlined as numbers:
                // SQLite binds floats as text, which breaks numeric comparison.
                $q->whereRaw(sprintf('%s >= %F', $effectivePrice, $min));
                if ($max !== null) {
                    $q->whereRaw(sprintf('%s <= %F', $effectivePrice, $max));
                }
            })
            ->when($this->sort === 'sale', fn (Builder $q) => $q->whereNotNull('sale_price')->whereColumn('sale_price', '<', 'price'))
            ->orderByRaw('stock = 0') // sold-out items last
            ->when($this->sort === 'price-asc', fn (Builder $q) => $q->orderByRaw("$effectivePrice asc"))
            ->when($this->sort === 'price-desc', fn (Builder $q) => $q->orderByRaw("$effectivePrice desc"))
            ->latest()
            ->paginate(min($this->perPage, 240), pageName: 'p', page: 1);

        return view('livewire.shop', [
            'products' => $products,
        ])->title($this->category?->name ?? 'Shop all');
    }
}
