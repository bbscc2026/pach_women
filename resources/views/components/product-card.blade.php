@props(['product'])

@php($images = $product->imageUrls())

<a href="{{ route('product', $product) }}" wire:navigate data-reveal {{ $attributes->class('group block') }}>
    <div class="relative aspect-[3/4] overflow-hidden bg-cream">
        @if ($images)
            <img src="{{ $images[0] }}" alt="{{ $product->name }}" loading="lazy"
                 @class(['size-full object-cover transition duration-700', 'group-hover:scale-105' => count($images) === 1, 'opacity-60 grayscale-[40%]' => ! $product->inStock()])>
            @if (count($images) > 1 && $product->inStock())
                {{-- Second photo fades in on hover (desktop only) --}}
                <img src="{{ $images[1] }}" alt="" loading="lazy" aria-hidden="true"
                     class="absolute inset-0 hidden size-full object-cover opacity-0 transition duration-500 group-hover:opacity-100 lg:block">
            @endif
        @else
            <div class="flex size-full items-center justify-center font-serif text-xl text-neutral-400">PACH</div>
        @endif

        <div class="absolute top-2 left-2 flex flex-col items-start gap-1 sm:top-3 sm:left-3">
            @if (! $product->inStock())
                <span class="bg-white px-2 py-1 text-[10px] tracking-widest uppercase">Sold out</span>
            @else
                @if ($product->onSale())
                    <span class="bg-sale px-2 py-1 text-[10px] tracking-widest text-white uppercase">{{ $product->discountPercent() }}% off</span>
                @endif
                @if ($product->created_at?->gt(now()->subDays(14)) && ! $product->onSale())
                    <span class="bg-ink px-2 py-1 text-[10px] tracking-widest text-white uppercase">New</span>
                @endif
            @endif
        </div>

        @if ($product->inStock() && $product->stock <= 3)
            <span class="absolute inset-x-0 bottom-0 flex items-center justify-center gap-1 bg-white/90 py-1.5 text-[10px] tracking-wider text-clay uppercase">
                <x-heroicon-o-fire class="size-3.5" /> Only {{ $product->stock }} left
            </span>
        @endif
    </div>

    <div class="mt-2.5 sm:mt-3">
        <h3 class="line-clamp-2 font-sans text-[13px] leading-snug text-neutral-800 group-hover:underline group-hover:underline-offset-4 sm:text-sm">{{ $product->name }}</h3>
        <p class="mt-1 flex flex-wrap items-baseline gap-x-2 text-sm">
            <span class="font-medium {{ $product->onSale() ? 'text-sale' : '' }}">{{ inr($product->finalPrice()) }}</span>
            @if ($product->onSale())
                <span class="text-xs text-neutral-400 line-through">{{ inr($product->price) }}</span>
            @endif
        </p>
    </div>
</a>
