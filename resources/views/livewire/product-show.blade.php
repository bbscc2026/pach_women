@php
    $images = $product->imageUrls();
    $whatsappText = rawurlencode("Hi PACH, I'm interested in {$product->name} (".route('product', $product).')');
@endphp

<div x-data="{ sticky: false, sizeGuide: false }"
     x-on:size-required.window="document.getElementById('sizes')?.scrollIntoView({ behavior: 'smooth', block: 'center' })"
     class="pb-24 lg:pb-0">
    <nav class="container-shop hidden pt-6 text-xs tracking-wide text-neutral-500 uppercase sm:block" aria-label="Breadcrumb">
        <a href="{{ route('home') }}" wire:navigate class="hover:text-ink">Home</a>
        <span class="mx-2">/</span>
        <a href="{{ route('category', $product->category) }}" wire:navigate class="hover:text-ink">{{ $product->category->name }}</a>
        <span class="mx-2">/</span>
        <span class="text-ink">{{ $product->name }}</span>
    </nav>

    <div class="sm:container-shop sm:mt-6 lg:grid lg:grid-cols-2 lg:gap-14">
        {{-- Gallery: swipe on phones, arrows + thumbnails on desktop --}}
        <div x-data="{
                active: 0,
                count: {{ max(count($images), 1) }},
                go(i) { this.active = (i + this.count) % this.count; this.$refs.track.scrollTo({ left: this.$refs.track.clientWidth * this.active, behavior: 'smooth' }) },
                sync() { this.active = Math.round(this.$refs.track.scrollLeft / this.$refs.track.clientWidth) },
             }"
             class="lg:sticky lg:top-24 lg:self-start" wire:ignore>
            <div class="group relative">
                <div x-ref="track" @scroll.debounce.50ms="sync()" class="scroll-row aspect-[3/4] bg-cream">
                    @forelse ($images as $i => $url)
                        <img src="{{ $url }}" alt="{{ $product->name }} – photo {{ $i + 1 }}" @if ($i > 0) loading="lazy" @endif
                             class="size-full shrink-0 snap-center object-cover">
                    @empty
                        <div class="flex size-full shrink-0 items-center justify-center font-serif text-3xl text-neutral-400">PACH</div>
                    @endforelse
                </div>

                @if (count($images) > 1)
                    <button type="button" @click="go(active - 1)" class="absolute top-1/2 left-3 hidden size-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 opacity-0 shadow transition group-hover:opacity-100 lg:flex" aria-label="Previous photo">
                        <x-heroicon-o-chevron-left class="size-5" />
                    </button>
                    <button type="button" @click="go(active + 1)" class="absolute top-1/2 right-3 hidden size-11 -translate-y-1/2 items-center justify-center rounded-full bg-white/90 opacity-0 shadow transition group-hover:opacity-100 lg:flex" aria-label="Next photo">
                        <x-heroicon-o-chevron-right class="size-5" />
                    </button>

                    <div class="absolute inset-x-0 bottom-4 flex justify-center gap-1.5 lg:hidden">
                        @foreach ($images as $i => $url)
                            <button type="button" @click="go({{ $i }})" class="h-1.5 rounded-full bg-white transition-all" :class="active === {{ $i }} ? 'w-6' : 'w-1.5 opacity-60'" aria-label="Photo {{ $i + 1 }}"></button>
                        @endforeach
                    </div>
                @endif

                @if ($product->onSale())
                    <span class="absolute top-3 left-3 bg-sale px-2 py-1 text-[10px] tracking-widest text-white uppercase">{{ $product->discountPercent() }}% off</span>
                @endif
            </div>

            @if (count($images) > 1)
                <div class="mt-3 hidden grid-cols-6 gap-2 lg:grid">
                    @foreach ($images as $i => $url)
                        <button type="button" @click="go({{ $i }})" class="aspect-[3/4] overflow-hidden border-2 transition"
                                :class="active === {{ $i }} ? 'border-ink' : 'border-transparent opacity-70 hover:opacity-100'" aria-label="Show photo {{ $i + 1 }}">
                            <img src="{{ $url }}" alt="" class="size-full object-cover">
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Details --}}
        <div class="px-4 pt-5 sm:px-0 lg:pt-0">
            <a href="{{ route('category', $product->category) }}" wire:navigate class="text-xs tracking-[0.2em] text-clay uppercase">{{ $product->category->name }}</a>
            <div class="mt-2 flex items-start justify-between gap-4">
                <h1 class="text-3xl leading-tight font-semibold sm:text-4xl">{{ $product->name }}</h1>
                <button type="button" class="-mr-2 flex size-11 shrink-0 items-center justify-center text-neutral-500 hover:text-ink" aria-label="Share"
                        @click="navigator.share ? navigator.share({ title: @js($product->name), url: location.href }) : navigator.clipboard.writeText(location.href).then(() => $dispatch('notify', { message: 'Link copied' }))">
                    <x-heroicon-o-share class="size-5" />
                </button>
            </div>

            <div class="mt-3 flex flex-wrap items-baseline gap-x-3 gap-y-1">
                <span class="text-2xl font-medium {{ $product->onSale() ? 'text-sale' : '' }}">{{ inr($product->finalPrice()) }}</span>
                @if ($product->onSale())
                    <span class="text-neutral-400 line-through">MRP {{ inr($product->price) }}</span>
                    <span class="text-sm font-medium text-sale">Save {{ inr((float) $product->price - $product->finalPrice()) }}</span>
                @endif
            </div>
            <p class="mt-1 text-xs text-neutral-500">Inclusive of all taxes</p>

            @if (! empty($product->sizes))
                <div id="sizes" class="mt-7 scroll-mt-28">
                    <div class="flex items-center justify-between">
                        <p class="label mb-0">Size @if ($size)<span class="ml-1 text-ink normal-case">· {{ $size }}</span>@endif</p>
                        @unless ($product->sizes === ['Free Size'])
                            <button type="button" @click="sizeGuide = true" class="flex items-center gap-1 text-xs underline underline-offset-4">
                                <x-heroicon-o-scale class="size-4" /> Size guide
                            </button>
                        @endunless
                    </div>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($product->sizes as $s)
                            <button type="button" wire:click="selectSize(@js($s))" aria-pressed="{{ $size === $s ? 'true' : 'false' }}"
                                    @class([
                                        'flex min-h-12 min-w-12 items-center justify-center border px-4 text-sm transition',
                                        'border-ink bg-ink text-white' => $size === $s,
                                        'border-sand hover:border-ink' => $size !== $s,
                                        'border-sale' => $errors->has('size') && $size !== $s,
                                    ])>
                                {{ $s }}
                            </button>
                        @endforeach
                    </div>
                    @error('size') <p class="error flex items-center gap-1"><x-heroicon-o-exclamation-circle class="size-4" /> {{ $message }}</p> @enderror
                </div>
            @endif

            @if ($product->inStock())
                <div class="mt-6 flex items-center gap-4">
                    <x-qty-stepper :quantity="$quantity" decrease="decrement" increase="increment" :max="$product->stock" />
                    @if ($product->stock <= 5)
                        <p class="flex items-center gap-1 text-sm text-clay"><x-heroicon-o-fire class="size-4" /> Only {{ $product->stock }} left</p>
                    @else
                        <p class="flex items-center gap-1 text-sm text-green-700"><x-heroicon-o-check-circle class="size-4" /> In stock</p>
                    @endif
                </div>

                <div x-intersect:leave="sticky = true" x-intersect:enter="sticky = false" class="mt-6 grid grid-cols-2 gap-3">
                    <button type="button" wire:click="addToCart" wire:loading.attr="disabled" wire:target="addToCart" class="btn-light">
                        <x-heroicon-o-shopping-bag class="size-5" wire:loading.remove wire:target="addToCart" />
                        <span wire:loading wire:target="addToCart" class="size-4 animate-spin rounded-full border-2 border-current border-t-transparent"></span>
                        Add to cart
                    </button>
                    <button type="button" wire:click="addToCart(true)" wire:loading.attr="disabled" wire:target="addToCart" class="btn-dark">
                        <x-heroicon-o-bolt class="size-5" /> Buy now
                    </button>
                </div>
            @else
                <div class="mt-7 flex items-start gap-3 border border-sand bg-cream px-4 py-4 text-sm">
                    <x-heroicon-o-bell-alert class="size-5 shrink-0" />
                    <p>This piece is sold out. Message us on WhatsApp and we'll tell you when it's back.</p>
                </div>
            @endif

            <a href="https://wa.me/{{ config('shop.contact.whatsapp') }}?text={{ $whatsappText }}" target="_blank" rel="noopener"
               class="mt-3 flex min-h-12 w-full items-center justify-center gap-2 border border-[#25D366] text-sm font-medium text-[#128C4B] transition hover:bg-[#25D366]/10">
                <x-heroicon-o-chat-bubble-left-right class="size-5" /> Ask about this on WhatsApp
            </a>

            {{-- Delivery promises --}}
            <ul class="mt-6 grid grid-cols-3 gap-2 text-center text-[11px] leading-tight text-neutral-600">
                <li class="flex flex-col items-center gap-1.5 bg-cream px-2 py-3"><x-heroicon-o-truck class="size-5 text-ink" />{{ inr(config('shop.free_shipping_over')) }}+ ships free</li>
                <li class="flex flex-col items-center gap-1.5 bg-cream px-2 py-3"><x-heroicon-o-banknotes class="size-5 text-ink" />Cash on delivery</li>
                <li class="flex flex-col items-center gap-1.5 bg-cream px-2 py-3"><x-heroicon-o-shield-check class="size-5 text-ink" />Secure checkout</li>
            </ul>

            {{-- Accordions --}}
            <div class="mt-6 divide-y divide-sand border-y border-sand text-sm">
                @if ($product->description)
                    <div x-data="{ open: true }">
                        <button type="button" @click="open = !open" class="flex w-full items-center justify-between py-4 text-left font-medium" :aria-expanded="open">
                            <span class="flex items-center gap-2"><x-heroicon-o-document-text class="size-5" /> Description</span>
                            <span wire:ignore.self class="transition" :class="open && 'rotate-180'"><x-heroicon-o-chevron-down class="size-4" /></span>
                        </button>
                        <div wire:ignore.self x-show="open" x-collapse>
                            <p class="pb-5 leading-relaxed whitespace-pre-line text-neutral-700">{{ $product->description }}</p>
                        </div>
                    </div>
                @endif

                <div x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="flex w-full items-center justify-between py-4 text-left font-medium" :aria-expanded="open">
                        <span class="flex items-center gap-2"><x-heroicon-o-swatch class="size-5" /> Product details</span>
                        <span wire:ignore.self class="transition" :class="open && 'rotate-180'"><x-heroicon-o-chevron-down class="size-4" /></span>
                    </button>
                    <div wire:ignore.self x-show="open" x-collapse x-cloak>
                        <dl class="space-y-2 pb-5 text-neutral-700">
                            @if ($product->fabric)<div class="flex justify-between"><dt class="text-neutral-500">Fabric</dt><dd>{{ $product->fabric }}</dd></div>@endif
                            @if ($product->color)<div class="flex justify-between"><dt class="text-neutral-500">Colour</dt><dd>{{ $product->color }}</dd></div>@endif
                            @if ($product->sku)<div class="flex justify-between"><dt class="text-neutral-500">Product code</dt><dd>{{ $product->sku }}</dd></div>@endif
                            <div class="flex justify-between"><dt class="text-neutral-500">Category</dt><dd>{{ $product->category->name }}</dd></div>
                        </dl>
                    </div>
                </div>

                <div x-data="{ open: false }">
                    <button type="button" @click="open = !open" class="flex w-full items-center justify-between py-4 text-left font-medium" :aria-expanded="open">
                        <span class="flex items-center gap-2"><x-heroicon-o-truck class="size-5" /> Shipping &amp; returns</span>
                        <span wire:ignore.self class="transition" :class="open && 'rotate-180'"><x-heroicon-o-chevron-down class="size-4" /></span>
                    </button>
                    <div wire:ignore.self x-show="open" x-collapse x-cloak>
                        <ul class="space-y-2 pb-5 text-neutral-700">
                            <li class="flex gap-2"><x-heroicon-o-clock class="mt-0.5 size-4 shrink-0 text-neutral-500" /> Dispatched in 1–3 working days; delivery in 2–8 days.</li>
                            <li class="flex gap-2"><x-heroicon-o-truck class="mt-0.5 size-4 shrink-0 text-neutral-500" /> Free shipping over {{ inr(config('shop.free_shipping_over')) }}, otherwise {{ inr(config('shop.shipping_fee')) }}.</li>
                            <li class="flex gap-2"><x-heroicon-o-arrow-uturn-left class="mt-0.5 size-4 shrink-0 text-neutral-500" /> No returns or refunds. Damaged or wrong items are replaced.
                                <a href="{{ route('page', 'returns') }}" wire:navigate class="underline underline-offset-4">Read policy</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if ($related->isNotEmpty())
        <section class="container-shop mt-16">
            <h2 class="section-title" data-reveal>You may also like</h2>
            <div class="scroll-row -mx-4 mt-6 gap-3 px-4 sm:mx-0 sm:grid sm:grid-cols-4 sm:gap-4 sm:overflow-visible sm:px-0">
                @foreach ($related as $item)
                    <div class="w-[44%] shrink-0 snap-start sm:w-auto" wire:key="rel-{{ $item->id }}">
                        <x-product-card :product="$item" />
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Sticky add-to-cart bar on phones once the main buttons scroll away --}}
    @if ($product->inStock())
        <div x-cloak wire:ignore.self x-show="sticky" x-transition:enter="transition duration-200" x-transition:enter-start="translate-y-full" x-transition:leave="transition duration-150" x-transition:leave-end="translate-y-full"
             class="fixed inset-x-0 bottom-0 z-40 border-t border-sand bg-white px-4 pt-3 shadow-[0_-4px_20px_rgba(0,0,0,0.06)] pb-safe lg:hidden">
            <div class="flex items-center gap-3">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-xs text-neutral-500">{{ $size ? 'Size '.$size : (empty($product->sizes) ? $product->name : 'Select a size') }}</p>
                    <p class="font-medium {{ $product->onSale() ? 'text-sale' : '' }}">{{ inr($product->finalPrice()) }}</p>
                </div>
                <button type="button" wire:click="addToCart" wire:loading.attr="disabled" wire:target="addToCart" class="btn-dark flex-1 px-4">
                    <x-heroicon-o-shopping-bag class="size-5" /> Add to cart
                </button>
            </div>
        </div>
    @endif

    {{-- Size guide --}}
    <div x-cloak wire:ignore.self x-show="sizeGuide" class="fixed inset-0 z-[60] flex items-end justify-center sm:items-center" role="dialog" aria-modal="true" aria-label="Size guide" @keydown.escape.window="sizeGuide = false">
        <div wire:ignore.self x-show="sizeGuide" x-transition.opacity class="absolute inset-0 bg-black/40" @click="sizeGuide = false"></div>
        <div wire:ignore.self x-show="sizeGuide" x-transition:enter="transition duration-300" x-transition:enter-start="translate-y-full sm:translate-y-4 sm:opacity-0"
             class="relative max-h-[85svh] w-full overflow-y-auto bg-white p-6 pb-safe sm:max-w-lg">
            <div class="mx-auto mb-4 h-1 w-10 rounded-full bg-sand sm:hidden"></div>
            <div class="flex items-center justify-between">
                <h2 class="font-serif text-2xl font-semibold">Size guide</h2>
                <button type="button" @click="sizeGuide = false" class="-mr-2 flex size-11 items-center justify-center" aria-label="Close size guide">
                    <x-heroicon-o-x-mark class="size-6" />
                </button>
            </div>
            <p class="mt-1 text-sm text-neutral-500">Body measurements in inches. If you're between sizes, pick the larger one.</p>
            <table class="mt-5 w-full text-center text-sm">
                <thead class="bg-cream text-xs tracking-wide uppercase">
                    <tr><th class="py-2.5">Size</th><th>Bust</th><th>Waist</th><th>Hip</th></tr>
                </thead>
                <tbody class="divide-y divide-sand">
                    @foreach ([['XS', 32, 26, 35], ['S', 34, 28, 37], ['M', 36, 30, 39], ['L', 38, 32, 41], ['XL', 40, 34, 43], ['XXL', 42, 36, 45]] as [$s, $bust, $waist, $hip])
                        <tr @class(['font-medium' => $size === $s])><td class="py-2.5">{{ $s }}</td><td>{{ $bust }}</td><td>{{ $waist }}</td><td>{{ $hip }}</td></tr>
                    @endforeach
                </tbody>
            </table>
            <a href="https://wa.me/{{ config('shop.contact.whatsapp') }}?text={{ $whatsappText }}" target="_blank" rel="noopener" class="mt-5 flex items-center gap-2 text-sm underline underline-offset-4">
                <x-heroicon-o-chat-bubble-left-right class="size-4" /> Not sure? Ask us on WhatsApp
            </a>
        </div>
    </div>
</div>
