<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;
use Livewire\Component;

/**
 * Full-screen search. Opens on the browser "open-search" event and shows
 * matching products while the customer types.
 */
class SearchOverlay extends Component
{
    public string $q = '';

    public function submit()
    {
        return $this->redirectRoute('shop', array_filter(['q' => trim($this->q)]), navigate: true);
    }

    public function render()
    {
        $term = trim($this->q);

        /** @var Collection<int, Product> $results */
        $results = mb_strlen($term) >= 2
            ? Product::active()
                ->where(fn ($q) => $q->where('name', 'like', "%{$term}%")->orWhere('sku', 'like', "%{$term}%"))
                ->orderByRaw('stock = 0')
                ->latest()
                ->limit(6)
                ->get()
            : collect();

        return view('livewire.search-overlay', [
            'term' => $term,
            'results' => $results,
            'categories' => $term === '' ? Category::active()->get() : collect(),
        ]);
    }
}
