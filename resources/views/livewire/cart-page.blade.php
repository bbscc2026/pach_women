<div class="container-shop py-6 sm:py-10 {{ $items->isNotEmpty() ? 'pb-28 lg:pb-10' : '' }}">
    <h1 class="flex items-center gap-3 text-4xl font-semibold">
        Your cart
        @if ($items->isNotEmpty())
            <span class="font-sans text-base font-normal text-neutral-500">({{ $items->sum('quantity') }} {{ Str::plural('item', $items->sum('quantity')) }})</span>
        @endif
    </h1>

    @if ($items->isEmpty())
        <div class="py-20 text-center">
            <div class="mx-auto flex size-24 items-center justify-center rounded-full bg-cream">
                <x-heroicon-o-shopping-bag class="size-10 text-neutral-400" />
            </div>
            <p class="mt-6 font-serif text-2xl">Your cart is empty</p>
            <p class="mt-2 text-sm text-neutral-500">Find something you love in the new collection.</p>
            <a href="{{ route('shop') }}" wire:navigate class="btn-dark mt-8">Start shopping <x-heroicon-o-arrow-right class="size-4" /></a>
        </div>
    @else
        <div class="mt-6 grid gap-8 lg:mt-8 lg:grid-cols-3 lg:gap-10">
            <div class="lg:col-span-2">
                <x-free-shipping-bar :gap="$freeShippingGap" :progress="$freeShippingProgress" class="mb-6 bg-cream px-4 py-4" />

                <ul class="divide-y divide-sand border-y border-sand" wire:loading.class="opacity-60">
                    @foreach ($items as $item)
                        <li wire:key="line-{{ $item->key }}" class="flex gap-4 py-5">
                            <a href="{{ route('product', $item->product) }}" wire:navigate class="w-24 shrink-0 sm:w-28">
                                <div class="aspect-[3/4] overflow-hidden bg-cream">
                                    @if ($item->product->thumbnailUrl())
                                        <img src="{{ $item->product->thumbnailUrl() }}" alt="{{ $item->product->name }}" class="size-full object-cover">
                                    @endif
                                </div>
                            </a>
                            <div class="flex min-w-0 flex-1 flex-col">
                                <div class="flex justify-between gap-3">
                                    <div class="min-w-0">
                                        <a href="{{ route('product', $item->product) }}" wire:navigate class="text-sm leading-snug hover:underline">{{ $item->product->name }}</a>
                                        @if ($item->size)
                                            <p class="mt-1 text-xs text-neutral-500">Size: {{ $item->size }}</p>
                                        @endif
                                        @if ($item->quantity === 0)
                                            <p class="mt-1 flex items-center gap-1 text-xs text-sale"><x-heroicon-o-exclamation-circle class="size-4" /> Sold out — please remove</p>
                                        @elseif ($item->quantity < $item->requested)
                                            <p class="mt-1 flex items-center gap-1 text-xs text-clay"><x-heroicon-o-exclamation-circle class="size-4" /> Only {{ $item->quantity }} left — quantity reduced</p>
                                        @endif
                                        <p class="mt-1 text-sm {{ $item->product->onSale() ? 'text-sale' : '' }}">{{ inr($item->price) }}</p>
                                    </div>
                                    <button type="button" wire:click="remove(@js($item->key))" class="-mt-2 -mr-2 flex size-10 shrink-0 items-center justify-center text-neutral-400 hover:text-ink" aria-label="Remove {{ $item->product->name }}">
                                        <x-heroicon-o-trash class="size-5" />
                                    </button>
                                </div>
                                <div class="mt-auto flex items-center justify-between pt-3">
                                    <x-qty-stepper size="sm" :quantity="$item->quantity" :max="$item->product->stock"
                                                   decrease="updateQuantity('{{ $item->key }}', {{ $item->quantity - 1 }})"
                                                   increase="updateQuantity('{{ $item->key }}', {{ $item->quantity + 1 }})" />
                                    <p class="text-sm font-medium">{{ inr($item->total) }}</p>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>

                <a href="{{ route('shop') }}" wire:navigate class="mt-6 inline-flex items-center gap-2 text-sm underline-offset-4 hover:underline">
                    <x-heroicon-o-arrow-left class="size-4" /> Continue shopping
                </a>
            </div>

            <aside class="h-fit bg-cream p-6 lg:sticky lg:top-24">
                <h2 class="font-serif text-2xl font-semibold">Order summary</h2>
                <div class="mt-6">
                    <x-order-summary :subtotal="$subtotal" :shipping="$shipping" :total="$total" />
                </div>
                <a href="{{ route('checkout') }}" wire:navigate class="btn-dark mt-6 hidden w-full lg:flex">
                    <x-heroicon-o-lock-closed class="size-4" /> Checkout
                </a>
                <ul class="mt-5 space-y-2 text-xs text-neutral-600">
                    <li class="flex items-center gap-2"><x-heroicon-o-banknotes class="size-4" /> Cash on delivery available</li>
                    <li class="flex items-center gap-2"><x-heroicon-o-shield-check class="size-4" /> Secure UPI, card &amp; netbanking</li>
                    <li class="flex items-center gap-2"><x-heroicon-o-arrow-uturn-left class="size-4" /> No returns — check sizes before ordering</li>
                </ul>
            </aside>
        </div>

        {{-- Sticky checkout bar on phones --}}
        <div class="fixed inset-x-0 bottom-0 z-40 border-t border-sand bg-white px-4 pt-3 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] pb-safe lg:hidden">
            <div class="flex items-center gap-4">
                <div>
                    <p class="text-xs text-neutral-500">Total</p>
                    <p class="text-lg font-medium">{{ inr($total) }}</p>
                </div>
                <a href="{{ route('checkout') }}" wire:navigate class="btn-dark flex-1">
                    <x-heroicon-o-lock-closed class="size-4" /> Checkout
                </a>
            </div>
        </div>
    @endif
</div>
