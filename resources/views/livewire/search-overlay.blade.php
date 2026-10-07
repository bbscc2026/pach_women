<div>
    <form wire:submit="submit" class="sticky top-0 border-b border-sand bg-white">
        <div class="container-shop flex h-16 items-center gap-3">
            <x-heroicon-o-magnifying-glass class="size-5 shrink-0 text-neutral-500" />
            <label for="site-search" class="sr-only">Search products</label>
            <input id="site-search" type="search" wire:model.live.debounce.300ms="q" autocomplete="off" enterkeyhint="search"
                   placeholder="Search kurtis, ponchos, festive wear…"
                   class="min-w-0 flex-1 border-0 bg-transparent text-base outline-none placeholder:text-neutral-400 focus:ring-0">
            <span wire:loading wire:target="q" class="size-4 animate-spin rounded-full border-2 border-sand border-t-ink" aria-hidden="true"></span>
            <button type="button" @click="open = false" class="-mr-2 flex size-11 items-center justify-center" aria-label="Close search">
                <x-heroicon-o-x-mark class="size-6" />
            </button>
        </div>
    </form>

    <div class="container-shop py-6">
        @if ($term === '')
            <p class="label">Browse categories</p>
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach ($categories as $category)
                    <a href="{{ route('category', $category) }}" wire:navigate @click="open = false" class="chip">{{ $category->name }}</a>
                @endforeach
                <a href="{{ route('shop', ['sort' => 'sale']) }}" wire:navigate @click="open = false" class="chip text-sale">
                    <x-heroicon-o-tag class="size-4" /> On sale
                </a>
            </div>
        @elseif (mb_strlen($term) < 2)
            <p class="text-sm text-neutral-500">Keep typing…</p>
        @elseif ($results->isEmpty())
            <div class="py-12 text-center">
                <x-heroicon-o-magnifying-glass class="mx-auto size-10 text-neutral-300" />
                <p class="mt-3 font-serif text-2xl">No results for “{{ $term }}”</p>
                <p class="mt-1 text-sm text-neutral-500">Try another word, or ask us on WhatsApp.</p>
            </div>
        @else
            <p class="label">Products</p>
            <ul class="mt-2 divide-y divide-sand">
                @foreach ($results as $product)
                    <li wire:key="sr-{{ $product->id }}">
                        <a href="{{ route('product', $product) }}" wire:navigate @click="open = false" class="flex items-center gap-4 py-3">
                            <div class="w-14 shrink-0">
                                <div class="aspect-[3/4] overflow-hidden bg-cream">
                                    @if ($product->thumbnailUrl())
                                        <img src="{{ $product->thumbnailUrl() }}" alt="" class="size-full object-cover">
                                    @endif
                                </div>
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="truncate text-sm">{{ $product->name }}</p>
                                <p class="mt-0.5 text-sm {{ $product->onSale() ? 'text-sale' : 'text-neutral-600' }}">
                                    {{ inr($product->finalPrice()) }}
                                    @unless ($product->inStock()) <span class="ml-1 text-xs text-neutral-400">· Sold out</span> @endunless
                                </p>
                            </div>
                            <x-heroicon-o-chevron-right class="size-4 text-neutral-400" />
                        </a>
                    </li>
                @endforeach
            </ul>
            <button type="submit" class="btn-light mt-6 w-full">See all results for “{{ $term }}”</button>
        @endif
    </div>
</div>
