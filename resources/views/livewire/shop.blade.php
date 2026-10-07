@php
    $sizes = \App\Livewire\Shop::SIZES;
    $priceRanges = \App\Livewire\Shop::PRICE_RANGES;
    $sorts = \App\Livewire\Shop::SORTS;
    $filterCount = $this->activeFilterCount();
@endphp

<div x-data="{ sheet: null }" x-effect="document.documentElement.classList.toggle('overflow-hidden', sheet !== null)" class="pb-8">
    <div class="container-shop pt-6 sm:pt-10">
        <nav class="hidden text-xs tracking-wide text-neutral-500 uppercase sm:block" aria-label="Breadcrumb">
            <a href="{{ route('home') }}" wire:navigate class="hover:text-ink">Home</a>
            <span class="mx-2">/</span>
            @if ($category)
                <a href="{{ route('shop') }}" wire:navigate class="hover:text-ink">Shop</a>
                <span class="mx-2">/</span>
                <span class="text-ink">{{ $category->name }}</span>
            @else
                <span class="text-ink">Shop</span>
            @endif
        </nav>

        <div class="sm:mt-6">
            <h1 class="text-4xl font-semibold sm:text-5xl">{{ $category?->name ?? 'Shop all' }}</h1>
            @if ($category?->description)
                <p class="mt-2 max-w-2xl text-sm text-neutral-600 sm:mt-3 sm:text-base">{{ $category->description }}</p>
            @endif
        </div>
    </div>

    {{-- Toolbar: sticky Filter / Sort on phones, inline controls on desktop --}}
    <div class="sticky top-16 z-30 mt-5 border-y border-sand bg-white/95 backdrop-blur">
        <div class="container-shop">
            <div class="grid grid-cols-2 divide-x divide-sand md:hidden">
                <button type="button" @click="sheet = 'filter'" class="flex h-12 items-center justify-center gap-2 text-xs tracking-wide uppercase">
                    <x-heroicon-o-adjustments-horizontal class="size-5" /> Filter
                    @if ($filterCount)<span class="flex size-5 items-center justify-center rounded-full bg-ink text-[10px] text-white">{{ $filterCount }}</span>@endif
                </button>
                <button type="button" @click="sheet = 'sort'" class="flex h-12 items-center justify-center gap-2 text-xs tracking-wide uppercase">
                    <x-heroicon-o-arrows-up-down class="size-5" /> {{ $sorts[$sort] ?? 'Sort' }}
                </button>
            </div>

            <div class="hidden grid-cols-4 gap-3 py-3 md:grid">
                <div class="relative">
                    <x-heroicon-o-magnifying-glass class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-neutral-400" />
                    <label for="search" class="sr-only">Search</label>
                    <input id="search" type="search" wire:model.live.debounce.400ms="search" placeholder="Search products" class="input pl-9">
                </div>
                <div>
                    <label for="size" class="sr-only">Size</label>
                    <select id="size" wire:model.live="size" class="input">
                        <option value="">All sizes</option>
                        @foreach ($sizes as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label for="price" class="sr-only">Price</label>
                    <select id="price" wire:model.live="price" class="input">
                        <option value="">Any price</option>
                        @foreach ($priceRanges as $key => [$label])<option value="{{ $key }}">{{ $label }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label for="sort" class="sr-only">Sort</label>
                    <select id="sort" wire:model.live="sort" class="input">
                        @foreach ($sorts as $key => $label)<option value="{{ $key }}">{{ $label }}</option>@endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="container-shop">
        {{-- Result count + active filter chips --}}
        <div class="mt-4 flex flex-wrap items-center gap-2 text-sm text-neutral-500">
            <p class="mr-auto">{{ $products->total() }} {{ Str::plural('product', $products->total()) }}</p>
            @if ($search)
                <button type="button" wire:click="$set('search', '')" class="chip min-h-8 px-3 normal-case">“{{ $search }}” <x-heroicon-o-x-mark class="size-3.5" /></button>
            @endif
            @if ($size)
                <button type="button" wire:click="$set('size', '')" class="chip min-h-8 px-3">Size {{ $size }} <x-heroicon-o-x-mark class="size-3.5" /></button>
            @endif
            @if ($price)
                <button type="button" wire:click="$set('price', '')" class="chip min-h-8 px-3 normal-case">{{ $priceRanges[$price][0] ?? $price }} <x-heroicon-o-x-mark class="size-3.5" /></button>
            @endif
            @if ($filterCount > 1)
                <button type="button" wire:click="clearFilters" class="text-xs underline underline-offset-4 hover:text-ink">Clear all</button>
            @endif
        </div>

        <div class="relative mt-5">
            <div wire:loading.flex wire:target="search, size, price, sort, clearFilters" class="absolute inset-x-0 top-10 z-10 hidden justify-center">
                <span class="size-6 animate-spin rounded-full border-2 border-sand border-t-ink"></span>
            </div>

            <div wire:loading.class="opacity-40" wire:target="search, size, price, sort, clearFilters" class="transition-opacity">
                @if ($products->isEmpty())
                    <div class="py-20 text-center">
                        <x-heroicon-o-face-frown class="mx-auto size-12 text-neutral-300" />
                        <p class="mt-4 font-serif text-2xl">No products found</p>
                        <p class="mt-2 text-sm text-neutral-500">Try a different size, price or search term.</p>
                        <button type="button" wire:click="clearFilters" class="btn-light mt-6">Clear filters</button>
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-x-3 gap-y-8 sm:gap-x-4 sm:gap-y-10 md:grid-cols-3 lg:grid-cols-4">
                        @foreach ($products as $product)
                            <x-product-card :product="$product" wire:key="p-{{ $product->id }}" />
                        @endforeach
                    </div>

                    <div class="mt-12 flex flex-col items-center gap-3">
                        <p class="text-xs text-neutral-500">Showing {{ $products->count() }} of {{ $products->total() }}</p>
                        <div class="h-0.5 w-40 overflow-hidden bg-sand">
                            <div class="h-full bg-ink" style="width: {{ round($products->count() / max($products->total(), 1) * 100) }}%"></div>
                        </div>
                        @if ($products->hasMorePages())
                            <button type="button" wire:click="loadMore" wire:loading.attr="disabled" wire:target="loadMore" class="btn-light mt-2 min-w-48">
                                <span wire:loading.remove wire:target="loadMore">Load more</span>
                                <span wire:loading wire:target="loadMore" class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
                            </button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Bottom sheets (phones) --}}
    <div x-cloak wire:ignore.self x-show="sheet !== null" class="fixed inset-0 z-[60] md:hidden" role="dialog" aria-modal="true" @keydown.escape.window="sheet = null">
        <div wire:ignore.self x-show="sheet !== null" x-transition.opacity class="absolute inset-0 bg-black/40" @click="sheet = null"></div>

        <div wire:ignore.self x-show="sheet === 'filter'" x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-y-full" x-transition:leave="transition duration-200" x-transition:leave-end="translate-y-full"
             class="absolute inset-x-0 bottom-0 flex max-h-[85svh] flex-col bg-white">
            <div class="flex items-center justify-between border-b border-sand px-5 py-3">
                <p class="flex items-center gap-2 font-serif text-2xl font-semibold"><x-heroicon-o-adjustments-horizontal class="size-6" /> Filter</p>
                <button type="button" @click="sheet = null" class="-mr-2 flex size-11 items-center justify-center" aria-label="Close filters"><x-heroicon-o-x-mark class="size-6" /></button>
            </div>
            <div class="flex-1 space-y-7 overflow-y-auto px-5 py-5">
                <div>
                    <label for="m-search" class="label">Search</label>
                    <div class="relative">
                        <x-heroicon-o-magnifying-glass class="pointer-events-none absolute top-1/2 left-3 size-4 -translate-y-1/2 text-neutral-400" />
                        <input id="m-search" type="search" wire:model.live.debounce.400ms="search" placeholder="e.g. cotton, red" class="input pl-9" enterkeyhint="search">
                    </div>
                </div>
                <div>
                    <p class="label">Size</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($sizes as $s)
                            <button type="button" wire:click="$set('size', @js($size === $s ? '' : $s))" class="{{ $size === $s ? 'chip-active' : 'chip' }} min-w-14 normal-case">{{ $s }}</button>
                        @endforeach
                    </div>
                </div>
                <div>
                    <p class="label">Price</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($priceRanges as $key => [$label])
                            <button type="button" wire:click="$set('price', @js($price === $key ? '' : $key))" class="{{ $price === $key ? 'chip-active' : 'chip' }} normal-case">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3 border-t border-sand px-5 pt-3 pb-safe">
                <button type="button" wire:click="clearFilters" class="btn-light">Clear</button>
                <button type="button" @click="sheet = null" class="btn-dark">
                    <span wire:loading.remove>Show {{ $products->total() }}</span>
                    <span wire:loading class="size-4 animate-spin rounded-full border-2 border-white border-t-transparent"></span>
                </button>
            </div>
        </div>

        <div wire:ignore.self x-show="sheet === 'sort'" x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-y-full" x-transition:leave="transition duration-200" x-transition:leave-end="translate-y-full"
             class="absolute inset-x-0 bottom-0 bg-white pb-safe">
            <div class="flex items-center justify-between border-b border-sand px-5 py-3">
                <p class="flex items-center gap-2 font-serif text-2xl font-semibold"><x-heroicon-o-arrows-up-down class="size-6" /> Sort by</p>
                <button type="button" @click="sheet = null" class="-mr-2 flex size-11 items-center justify-center" aria-label="Close sort"><x-heroicon-o-x-mark class="size-6" /></button>
            </div>
            <ul class="px-2 py-2">
                @foreach ($sorts as $key => $label)
                    <li>
                        <button type="button" wire:click="$set('sort', @js($key))" @click="sheet = null" class="flex min-h-12 w-full items-center justify-between px-3 text-left text-sm {{ $sort === $key ? 'font-medium' : '' }}">
                            {{ $label }}
                            @if ($sort === $key)<x-heroicon-o-check class="size-5" />@endif
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
