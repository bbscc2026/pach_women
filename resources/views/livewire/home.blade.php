<div>
    {{-- Phone: app-style search pill above the slider --}}
    <div class="container-shop py-3 lg:hidden">
        <button type="button" x-data x-on:click="$dispatch('open-search')"
                class="flex h-11 w-full items-center gap-3 rounded-full border border-sand bg-cream px-4 text-left text-sm text-neutral-500 active:bg-sand">
            <x-heroicon-o-magnifying-glass class="size-5 text-neutral-600" />
            Search kurtis, ponchos, festive…
        </button>
    </div>

    {{-- Hero slider: auto-plays through the visible banners (Admin → Home banners) --}}
    @php
        $slides = $banners->map(fn ($b) => [
            'title' => $b->title,
            'subtitle' => $b->subtitle,
            'image' => $b->imageUrl(),
            'button' => $b->button_text ?: 'Shop the collection',
            'link' => $b->link ?: route('shop'),
        ]);
        if ($slides->isEmpty()) {
            $slides = collect([[
                'title' => 'Grace in every thread',
                'subtitle' => 'Kurtis, tunics, ponchos and festive wear, picked for comfort and made to be noticed.',
                'image' => $newArrivals->first()?->thumbnailUrl(),
                'button' => 'Shop the collection',
                'link' => route('shop'),
            ]]);
        }
        $interval = (int) round(max(2, (float) config('shop.hero_interval', 5.5)) * 1000);
    @endphp
    <section x-data="{
                i: 0,
                n: {{ $slides->count() }},
                paused: false,
                timer: null,
                startX: null,
                auto: false,
                init() {
                    this.auto = this.n > 1 && ! window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    this.schedule();
                },
                destroy() { clearTimeout(this.timer) },
                // One timer per slide, restarted on every change so it matches the progress bar.
                schedule() {
                    clearTimeout(this.timer);
                    if (! this.auto || this.paused) return;
                    this.timer = setTimeout(() => document.hidden ? this.schedule() : this.next(), {{ $interval }});
                },
                pause() { this.paused = true; clearTimeout(this.timer) },
                resume() { this.paused = false; this.schedule() },
                go(k) { this.i = (k + this.n) % this.n; this.schedule() },
                next() { this.go(this.i + 1) },
                prev() { this.go(this.i - 1) },
                swipe(endX) {
                    if (this.startX === null) return;
                    const dx = endX - this.startX;
                    if (Math.abs(dx) > 40) dx < 0 ? this.next() : this.prev();
                    this.startX = null;
                },
             }"
             @mouseenter="pause()" @mouseleave="resume()"
             @touchstart.passive="startX = $event.touches[0].clientX; pause()"
             @touchend="paused = false; swipe($event.changedTouches[0].clientX); schedule()"
             @keydown.left="prev()" @keydown.right="next()"
             class="relative mx-4 overflow-hidden rounded-3xl bg-cream md:mx-0 md:rounded-none" aria-roledescription="carousel" aria-label="Featured collections">
        <div class="grid">
            @foreach ($slides as $k => $slide)
                <div @class(['col-start-1 row-start-1 transition-opacity duration-700 ease-out', 'opacity-0 pointer-events-none' => $k > 0])
                     :class="i === {{ $k }} ? 'opacity-100! z-10 pointer-events-auto!' : 'opacity-0 pointer-events-none'"
                     role="group" aria-roledescription="slide" aria-label="{{ $k + 1 }} of {{ $slides->count() }}">
                    <div class="relative md:container-shop md:grid md:grid-cols-2 md:items-center md:gap-12 md:py-16">
                        <div class="relative h-[58svh] max-h-[560px] min-h-[380px] overflow-hidden bg-sand md:order-2 md:aspect-[4/5] md:h-auto md:max-h-none md:min-h-0">
                            @if ($slide['image'])
                                <img src="{{ $slide['image'] }}" alt="{{ $slide['title'] }}" @if ($k === 0) fetchpriority="high" @else loading="lazy" @endif
                                     class="size-full object-cover transition-transform duration-[6000ms] ease-out"
                                     :class="i === {{ $k }} ? 'scale-100' : 'scale-110'">
                            @endif
                            <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-black/10 to-transparent md:hidden"></div>
                        </div>

                        <div class="absolute inset-x-0 bottom-0 p-6 pb-14 text-white md:static md:order-1 md:p-0 md:text-ink">
                            <div class="transition duration-700 ease-out" :class="i === {{ $k }} ? 'translate-y-0 opacity-100' : 'translate-y-4 opacity-0'">
                                <p class="flex items-center gap-2 text-xs tracking-[0.3em] uppercase md:text-clay">
                                    <x-heroicon-o-sparkles class="size-4" /> {{ $loop->first ? 'New season' : 'Featured' }}
                                </p>
                                @if ($loop->first)
                                    <h1 class="mt-3 text-4xl leading-[1.05] font-semibold sm:text-5xl lg:text-6xl">{{ $slide['title'] }}</h1>
                                @else
                                    <h2 class="mt-3 text-4xl leading-[1.05] font-semibold sm:text-5xl lg:text-6xl">{{ $slide['title'] }}</h2>
                                @endif
                                @if ($slide['subtitle'])
                                    <p class="mt-3 max-w-md text-sm text-white/85 md:mt-5 md:text-base md:text-neutral-600">{{ $slide['subtitle'] }}</p>
                                @endif
                                <div class="mt-6 flex flex-wrap gap-3">
                                    <a href="{{ $slide['link'] }}" wire:navigate class="btn bg-white text-ink hover:bg-cream md:bg-ink md:text-white md:hover:bg-black/80">
                                        {{ $slide['button'] }} <x-heroicon-o-arrow-right class="size-4" />
                                    </a>
                                    @if ($loop->first && $onSale->isNotEmpty())
                                        <a href="{{ route('shop', ['sort' => 'sale']) }}" wire:navigate class="btn border border-white/70 text-white hover:bg-white/10 md:border-ink md:text-ink md:hover:bg-ink md:hover:text-white">
                                            <x-heroicon-o-tag class="size-4" /> Sale
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($slides->count() > 1)
            {{-- Progress indicators: the active bar fills while the slide is showing --}}
            <div class="absolute inset-x-0 bottom-5 z-20 md:bottom-8">
                <div class="container-shop flex items-center gap-2">
                    @foreach ($slides as $k => $slide)
                        <button type="button" @click="go({{ $k }})" class="group py-2" aria-label="Show slide {{ $k + 1 }}">
                            <span class="block h-0.5 overflow-hidden bg-white/40 transition-all md:bg-ink/15" :class="i === {{ $k }} ? 'w-10' : 'w-5 group-hover:w-7'">
                                <span class="block h-full bg-white md:bg-ink" style="animation-duration: {{ $interval }}ms" :class="i === {{ $k }} ? (paused || ! auto ? 'w-full' : 'animate-hero-progress') : 'w-0'"></span>
                            </span>
                        </button>
                    @endforeach
                    <span class="ml-2 text-xs text-white/80 tabular-nums md:text-neutral-500"><span x-text="i + 1">1</span> / {{ $slides->count() }}</span>
                </div>
            </div>

            {{-- Arrows (desktop) --}}
            <div class="pointer-events-none absolute inset-x-0 top-1/2 z-20 hidden -translate-y-1/2 lg:block">
                <div class="mx-auto flex max-w-[86rem] justify-between px-4">
                    <button type="button" @click="prev()" class="pointer-events-auto flex size-11 items-center justify-center rounded-full bg-white/90 shadow transition hover:bg-white" aria-label="Previous slide">
                        <x-heroicon-o-chevron-left class="size-5" />
                    </button>
                    <button type="button" @click="next()" class="pointer-events-auto flex size-11 items-center justify-center rounded-full bg-white/90 shadow transition hover:bg-white" aria-label="Next slide">
                        <x-heroicon-o-chevron-right class="size-5" />
                    </button>
                </div>
            </div>
        @endif
    </section>

    {{-- Categories: swipeable circles on phones --}}
    @if ($categories->isNotEmpty())
        <section class="container-shop pt-10 sm:pt-16">
            <div class="flex items-end justify-between" data-reveal>
                <h2 class="section-title">Shop by category</h2>
                <a href="{{ route('shop') }}" wire:navigate class="nudge flex items-center gap-1 text-sm underline-offset-4 hover:underline">View all <x-heroicon-o-arrow-right class="size-4" /></a>
            </div>
            <div class="scroll-row -mx-4 mt-6 gap-4 px-4 sm:mx-0 sm:grid sm:grid-cols-3 sm:gap-6 sm:overflow-visible sm:px-0 lg:grid-cols-6">
                @foreach ($categories as $category)
                    <a href="{{ route('category', $category) }}" wire:navigate class="group w-24 shrink-0 snap-start sm:w-auto" data-reveal>
                        <div class="aspect-square overflow-hidden rounded-full bg-cream ring-1 ring-sand ring-offset-2 transition group-hover:ring-ink">
                            @if ($category->imageUrl())
                                <img src="{{ $category->imageUrl() }}" alt="" loading="lazy" class="size-full object-cover transition duration-500 group-hover:scale-105">
                            @endif
                        </div>
                        <p class="mt-3 text-center text-xs tracking-wide uppercase sm:text-sm">{{ $category->name }}</p>
                        <p class="hidden text-center text-xs text-neutral-500 sm:block">{{ $category->products_count }} styles</p>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- New arrivals --}}
    @if ($newArrivals->isNotEmpty())
        <section class="container-shop pt-12 sm:pt-16">
            <div class="flex items-end justify-between" data-reveal>
                <h2 class="section-title">New arrivals</h2>
                <a href="{{ route('shop') }}" wire:navigate class="nudge flex items-center gap-1 text-sm underline-offset-4 hover:underline">Shop all <x-heroicon-o-arrow-right class="size-4" /></a>
            </div>
            <div class="mt-6 grid grid-cols-2 gap-x-3 gap-y-8 sm:gap-x-4 sm:gap-y-10 md:grid-cols-3 lg:grid-cols-4">
                @foreach ($newArrivals as $product)
                    <x-product-card :product="$product" wire:key="new-{{ $product->id }}" />
                @endforeach
            </div>
        </section>
    @endif

    {{-- Sale: swipe row on phones --}}
    @if ($onSale->isNotEmpty())
        <section class="mt-14 bg-cream py-12 sm:py-16">
            <div class="container-shop">
                <div class="flex items-end justify-between" data-reveal>
                    <div>
                        <p class="flex items-center gap-2 text-xs tracking-[0.3em] text-sale uppercase"><x-heroicon-o-tag class="size-4" /> Limited offers</p>
                        <h2 class="section-title mt-2">On sale</h2>
                    </div>
                    <a href="{{ route('shop', ['sort' => 'sale']) }}" wire:navigate class="nudge flex items-center gap-1 text-sm underline-offset-4 hover:underline">See all <x-heroicon-o-arrow-right class="size-4" /></a>
                </div>
                <div class="scroll-row -mx-4 mt-6 gap-3 px-4 sm:mx-0 sm:grid sm:grid-cols-3 sm:gap-4 sm:overflow-visible sm:px-0 lg:grid-cols-4">
                    @foreach ($onSale as $product)
                        <div class="w-[44%] shrink-0 snap-start sm:w-auto" wire:key="sale-{{ $product->id }}">
                            <x-product-card :product="$product" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Instagram / store --}}
    <section class="container-shop pt-12 sm:pt-16">
        <div class="grid gap-3 md:grid-cols-2 md:gap-4">
            <a href="{{ config('shop.contact.instagram') }}" target="_blank" rel="noopener" class="group flex items-center justify-between gap-6 bg-ink p-7 text-white transition hover:-translate-y-0.5 sm:p-10" data-reveal>
                <div>
                    <p class="text-xs tracking-[0.3em] text-white/60 uppercase">Follow us</p>
                    <p class="mt-2 font-serif text-3xl">@pach_women</p>
                    <p class="mt-2 text-sm text-white/70">New drops, client diaries and offers, first on Instagram.</p>
                </div>
                <x-heroicon-o-arrow-up-right class="size-6 shrink-0 transition group-hover:translate-x-1 group-hover:-translate-y-1" />
            </a>
            <a href="{{ config('shop.contact.store_map') }}" target="_blank" rel="noopener" class="group flex items-center justify-between gap-6 bg-sand p-7 transition hover:-translate-y-0.5 sm:p-10" data-reveal>
                <div>
                    <p class="flex items-center gap-2 text-xs tracking-[0.3em] text-neutral-500 uppercase"><x-heroicon-o-map-pin class="size-4" /> Visit the store</p>
                    <p class="mt-2 font-serif text-3xl">Koshak, Nadapuram</p>
                    <p class="mt-2 text-sm text-neutral-600">{{ config('shop.contact.store') }}</p>
                </div>
                <x-heroicon-o-arrow-up-right class="size-6 shrink-0 transition group-hover:translate-x-1 group-hover:-translate-y-1" />
            </a>
        </div>
    </section>
</div>
