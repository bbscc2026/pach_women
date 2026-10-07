<div class="flex h-full flex-col">
    <div class="flex items-center justify-between border-b border-sand px-5 py-4">
        <p class="flex items-center gap-2 font-serif text-2xl font-semibold">
            <x-heroicon-o-shopping-bag class="size-6" />
            Your cart
            @if ($items->isNotEmpty())
                <span class="font-sans text-sm font-normal text-neutral-500">({{ $items->sum('quantity') }})</span>
            @endif
        </p>
        <button type="button" @click="open = false" class="-mr-2 flex size-11 items-center justify-center" aria-label="Close cart">
            <x-heroicon-o-x-mark class="size-6" />
        </button>
    </div>

    @if ($items->isEmpty())
        <div class="flex flex-1 flex-col items-center justify-center px-8 text-center">
            <div class="flex size-20 items-center justify-center rounded-full bg-cream">
                <x-heroicon-o-shopping-bag class="size-9 text-neutral-400" />
            </div>
            <p class="mt-5 font-serif text-2xl">Your cart is empty</p>
            <p class="mt-1 text-sm text-neutral-500">Find something you love in the new collection.</p>
            <a href="{{ route('shop') }}" wire:navigate @click="open = false" class="btn-dark mt-6">Start shopping</a>
        </div>
    @else
        <x-free-shipping-bar :gap="$freeShippingGap" :progress="$freeShippingProgress" class="border-b border-sand bg-cream px-5 py-4" />

        <ul class="flex-1 divide-y divide-sand overflow-y-auto px-5" wire:loading.class="opacity-60">
            @foreach ($items as $item)
                <li wire:key="drawer-{{ $item->key }}" class="flex gap-4 py-4">
                    <a href="{{ route('product', $item->product) }}" wire:navigate @click="open = false" class="w-20 shrink-0">
                        <div class="aspect-[3/4] overflow-hidden bg-cream">
                            @if ($item->product->thumbnailUrl())
                                <img src="{{ $item->product->thumbnailUrl() }}" alt="{{ $item->product->name }}" class="size-full object-cover">
                            @endif
                        </div>
                    </a>
                    <div class="flex min-w-0 flex-1 flex-col">
                        <div class="flex justify-between gap-3">
                            <p class="text-sm leading-snug">{{ $item->product->name }}</p>
                            <button type="button" wire:click="remove(@js($item->key))" class="-mt-1 -mr-1 flex size-8 shrink-0 items-center justify-center text-neutral-400 hover:text-ink" aria-label="Remove {{ $item->product->name }}">
                                <x-heroicon-o-x-mark class="size-4" />
                            </button>
                        </div>
                        @if ($item->size)
                            <p class="mt-0.5 text-xs text-neutral-500">Size: {{ $item->size }}</p>
                        @endif
                        @if ($item->quantity === 0)
                            <p class="mt-1 flex items-center gap-1 text-xs text-sale"><x-heroicon-o-exclamation-circle class="size-4" /> Sold out — please remove</p>
                        @elseif ($item->quantity < $item->requested)
                            <p class="mt-1 flex items-center gap-1 text-xs text-clay"><x-heroicon-o-exclamation-circle class="size-4" /> Only {{ $item->quantity }} left — quantity reduced</p>
                        @endif
                        <div class="mt-auto flex items-center justify-between pt-2">
                            <x-qty-stepper size="sm" :quantity="$item->quantity" :max="$item->product->stock"
                                           decrease="updateQuantity('{{ $item->key }}', {{ $item->quantity - 1 }})"
                                           increase="updateQuantity('{{ $item->key }}', {{ $item->quantity + 1 }})" />
                            <p class="text-sm font-medium">{{ inr($item->total) }}</p>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>

        <div class="border-t border-sand px-5 pt-4 pb-safe">
            <div class="flex justify-between text-base font-medium">
                <span>Subtotal</span>
                <span>{{ inr($subtotal) }}</span>
            </div>
            <p class="mt-1 text-xs text-neutral-500">Shipping calculated at checkout. Cash on delivery available.</p>
            <a href="{{ route('checkout') }}" wire:navigate @click="open = false" class="btn-dark mt-4 w-full">
                <x-heroicon-o-lock-closed class="size-4" />
                Checkout
            </a>
            <a href="{{ route('cart') }}" wire:navigate @click="open = false" class="mt-3 block text-center text-sm underline underline-offset-4">View full cart</a>
        </div>
    @endif
</div>
