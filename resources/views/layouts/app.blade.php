@props(['title' => null, 'description' => null])

@php
    $navCategories = once(fn () => \App\Models\Category::active()->limit(6)->get());
    // Pages with their own sticky action bar on phones hide the bottom tab bar.
    $showTabBar = ! request()->routeIs('product', 'checkout', 'cart');
    $placeholders = \App\Models\ContentPage::placeholders();
    $announcements = collect(config('shop.announcements'))->filter()->map(fn ($text) => strtr($text, $placeholders))->values()->all();
    $footerPages = once(fn () => \App\Models\ContentPage::active()->get(['slug', 'title']));
    // Phones get an app-style bar: logo on Home, back arrow + page title everywhere else.
    $isHome = request()->routeIs('home');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#141414">

    <title>{{ $title ? $title.' | '.config('shop.name') : config('shop.name').' | Women’s Clothing Online' }}</title>
    <meta name="description" content="{{ $description ?? 'Shop kurtis, tunics, ponchos, shawls, western and festive wear from PACH WOMEN. Cash on delivery and fast shipping across India.' }}">

    @fonts
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if (\App\Support\Razorpay::enabled())
        <script src="https://checkout.razorpay.com/v1/checkout.js" defer data-navigate-once></script>
    @endif
    {{-- Installable app (works offline, updates itself) --}}
    <link rel="manifest" href="/site.webmanifest">
    <link rel="icon" type="image/png" href="/icons/icon-192.png">
    <link rel="apple-touch-icon" href="/icons/apple-touch-icon.png">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black">
    <meta name="apple-mobile-web-app-title" content="PACH">
    <script src="/pwa.js" defer data-navigate-once></script>
    @stack('head')
</head>
<body class="flex min-h-screen flex-col {{ $showTabBar ? 'pb-16 lg:pb-0' : '' }}">
    {{-- Announcement bar: rotates on phones, all messages on desktop (Admin → Site settings) --}}
    @if ($announcements)
    <div @class(['bg-ink text-[11px] tracking-[0.2em] text-white uppercase', 'hidden md:block' => ! $isHome])>
        <div class="container-shop hidden h-9 items-center justify-center gap-8 md:flex">
            @foreach ($announcements as $text)
                <span class="flex items-center gap-2">
                    @if (! $loop->first)<span class="text-white/30">&bull;</span>@endif
                    {{ $text }}
                </span>
            @endforeach
        </div>
        <div x-data="{ i: 0, n: {{ count($announcements) }} }" x-init="setInterval(() => i = (i + 1) % n, 3500)" class="relative h-9 overflow-hidden md:hidden">
            @foreach ($announcements as $index => $text)
                <p x-show="i === {{ $index }}" @if ($index > 0) x-cloak @endif
                   x-transition:enter="transition duration-500" x-transition:enter-start="translate-y-full opacity-0"
                   x-transition:leave="transition duration-500 absolute inset-x-0" x-transition:leave-end="-translate-y-full opacity-0"
                   class="flex h-9 items-center justify-center">{{ $text }}</p>
            @endforeach
        </div>
    </div>
    @endif

    <header x-data="{ menu: false, scrolled: false }" x-effect="document.documentElement.classList.toggle('overflow-hidden', menu)"
            @scroll.window.throttle.100ms="scrolled = window.scrollY > 8"
            :class="scrolled && 'shadow-[0_6px_24px_-12px_rgba(0,0,0,0.18)]'"
            class="app-chrome sticky top-0 z-40 border-b border-sand bg-white/95 backdrop-blur transition-shadow duration-300">
        <div @class([
            'container-shop grid h-14 items-center gap-2 lg:flex lg:h-16 lg:justify-between lg:gap-4',
            'grid-cols-[1fr_auto_1fr]' => $isHome,
            'grid-cols-[auto_1fr_auto]' => ! $isHome,
        ])>
            <div class="flex items-center lg:hidden">
                @if ($isHome)
                    <button type="button" class="-ml-2.5 flex size-11 items-center justify-center" @click="menu = true" aria-label="Open menu">
                        <x-heroicon-o-bars-3 class="size-6" />
                    </button>
                @else
                    {{-- Back: previous screen, or Home when opened directly from a link --}}
                    <button type="button" class="-ml-2.5 flex size-11 items-center justify-center rounded-full active:bg-cream" aria-label="Back"
                            @click="window.pachBack()">
                        <x-heroicon-o-chevron-left class="size-6" />
                    </button>
                @endif
            </div>

            <a href="{{ route('home') }}" wire:navigate @class(['flex-col items-center leading-none lg:flex lg:items-start', 'flex' => $isHome, 'hidden' => ! $isHome]) aria-label="PACH WOMEN home">
                <span class="font-serif text-2xl font-semibold tracking-[0.3em]">PACH</span>
                <span class="text-[9px] tracking-[0.55em] text-neutral-500">WOMEN</span>
            </a>
            @unless ($isHome)
                <p class="truncate text-center text-[15px] font-medium lg:hidden">{{ $title ?? config('shop.name') }}</p>
            @endunless

            <nav class="hidden items-center gap-7 text-[13px] tracking-wide uppercase lg:flex">
                <a href="{{ route('shop') }}" wire:navigate @class(['py-2 hover:text-clay', 'border-b border-ink' => request()->routeIs('shop')])>Shop all</a>
                @foreach ($navCategories as $navCategory)
                    <a href="{{ route('category', $navCategory) }}" wire:navigate
                       @class(['py-2 hover:text-clay', 'border-b border-ink' => request()->is('category/'.$navCategory->slug)])>{{ $navCategory->name }}</a>
                @endforeach
                <a href="{{ route('shop', ['sort' => 'sale']) }}" wire:navigate class="py-2 text-sale hover:opacity-80">Sale</a>
            </nav>

            <div class="flex items-center justify-end">
                <button type="button" class="flex size-11 items-center justify-center" @click="$dispatch('open-search')" aria-label="Search">
                    <x-heroicon-o-magnifying-glass class="size-6" />
                </button>
                <a href="{{ route('me') }}" wire:navigate class="hidden size-11 items-center justify-center lg:flex" aria-label="My account">
                    <x-heroicon-o-user class="size-6" />
                </a>
                <div class="-mr-2.5">
                    <livewire:cart-count />
                </div>
            </div>
        </div>

        {{-- Mobile menu --}}
        {{-- Teleported to <body>: the header's backdrop blur would otherwise clip a fixed panel to the header height. --}}
        <template x-teleport="body">
        <div x-cloak x-show="menu" class="fixed inset-0 z-50 lg:hidden" role="dialog" aria-modal="true" aria-label="Menu">
            <div class="absolute inset-0 bg-black/40" @click="menu = false" x-show="menu" x-transition.opacity></div>
            <div x-show="menu"
                 x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="-translate-x-full"
                 x-transition:leave="transition duration-200 ease-in" x-transition:leave-end="-translate-x-full"
                 class="absolute inset-y-0 left-0 flex w-[85%] max-w-sm flex-col overflow-y-auto bg-white">
                <div class="flex items-center justify-between border-b border-sand px-5 py-4">
                    <span class="font-serif text-xl font-semibold tracking-[0.3em]">PACH</span>
                    <button type="button" class="-mr-2 flex size-11 items-center justify-center" @click="menu = false" aria-label="Close menu">
                        <x-heroicon-o-x-mark class="size-6" />
                    </button>
                </div>

                <nav class="flex-1 px-5 py-4">
                    <p class="label">Shop</p>
                    <a href="{{ route('shop') }}" wire:navigate class="flex items-center justify-between border-b border-sand py-3.5">
                        <span class="flex items-center gap-3"><x-heroicon-o-squares-2x2 class="size-5 text-neutral-500" /> All products</span>
                        <x-heroicon-o-chevron-right class="size-4 text-neutral-400" />
                    </a>
                    @foreach ($navCategories as $navCategory)
                        <a href="{{ route('category', $navCategory) }}" wire:navigate class="flex items-center justify-between border-b border-sand py-3">
                            <span class="flex items-center gap-3">
                                <span class="size-8 overflow-hidden rounded-full bg-cream">
                                    @if ($navCategory->imageUrl())<img src="{{ $navCategory->imageUrl() }}" alt="" class="size-full object-cover">@endif
                                </span>
                                {{ $navCategory->name }}
                            </span>
                            <x-heroicon-o-chevron-right class="size-4 text-neutral-400" />
                        </a>
                    @endforeach
                    <a href="{{ route('shop', ['sort' => 'sale']) }}" wire:navigate class="flex items-center justify-between border-b border-sand py-3.5 text-sale">
                        <span class="flex items-center gap-3"><x-heroicon-o-tag class="size-5" /> Sale</span>
                        <x-heroicon-o-chevron-right class="size-4" />
                    </a>

                    <p class="label mt-8">Account</p>
                    @auth
                        <a href="{{ route('account.orders') }}" wire:navigate class="flex items-center gap-3 py-3"><x-heroicon-o-clipboard-document-list class="size-5 text-neutral-500" /> My orders</a>
                        <a href="{{ route('account.addresses') }}" wire:navigate class="flex items-center gap-3 py-3"><x-heroicon-o-map-pin class="size-5 text-neutral-500" /> Addresses</a>
                        <a href="{{ route('account.profile') }}" wire:navigate class="flex items-center gap-3 py-3"><x-heroicon-o-user class="size-5 text-neutral-500" /> Profile</a>
                    @else
                        <a href="{{ route('login') }}" wire:navigate class="flex items-center gap-3 py-3"><x-heroicon-o-arrow-right-end-on-rectangle class="size-5 text-neutral-500" /> Log in</a>
                        <a href="{{ route('register') }}" wire:navigate class="flex items-center gap-3 py-3"><x-heroicon-o-user-plus class="size-5 text-neutral-500" /> Create account</a>
                    @endauth
                </nav>

                <div class="space-y-3 border-t border-sand bg-cream px-5 py-5 text-sm pb-safe">
                    <button type="button" data-pwa-install class="items-center gap-3 font-medium"><x-heroicon-o-device-phone-mobile class="size-5" /> Install the PACH app</button>
                    <p data-pwa-ios-hint class="text-xs text-neutral-600">Install the app: tap <strong>Share</strong> <x-heroicon-o-arrow-up-on-square class="inline size-4 align-text-bottom" /> then <strong>Add to Home Screen</strong>.</p>
                    <a href="https://wa.me/{{ config('shop.contact.whatsapp') }}" target="_blank" rel="noopener" class="flex items-center gap-3"><x-heroicon-o-chat-bubble-left-right class="size-5" /> Chat on WhatsApp</a>
                    <a href="{{ config('shop.contact.instagram') }}" target="_blank" rel="noopener" class="flex items-center gap-3"><x-heroicon-o-camera class="size-5" /> @pach_women</a>
                    <a href="{{ config('shop.contact.store_map') }}" target="_blank" rel="noopener" class="flex items-center gap-3"><x-heroicon-o-map-pin class="size-5" /> Visit our store</a>
                </div>
            </div>
        </div>
        </template>
    </header>

    <main class="flex-1">
        {{ $slot }}
    </main>

    {{-- Trust strip + footer: desktop/tablet only. On phones the same links live in the "Me" tab. --}}
    <section class="mt-16 hidden border-y border-sand lg:block">
        <div class="container-shop grid grid-cols-2 gap-x-4 gap-y-6 py-8 lg:grid-cols-4">
            @foreach ([
                ['truck', 'Fast delivery', 'All over India'],
                ['banknotes', 'Cash on delivery', 'Pay when it arrives'],
                ['shield-check', 'Secure payments', 'UPI, cards, netbanking'],
                ['chat-bubble-left-right', 'Help on WhatsApp', '10 AM – 7 PM, Mon–Sat'],
            ] as [$icon, $heading, $sub])
                <div class="group flex items-center gap-3" data-reveal>
                    <div class="flex size-11 shrink-0 items-center justify-center rounded-full bg-cream transition duration-300 group-hover:scale-110 group-hover:bg-sand">
                        <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-5" />
                    </div>
                    <div>
                        <p class="text-sm font-medium">{{ $heading }}</p>
                        <p class="text-xs text-neutral-500">{{ $sub }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <footer class="hidden bg-cream lg:block">
        <div class="container-shop grid gap-0 py-10 sm:grid-cols-2 sm:gap-10 lg:grid-cols-4 lg:py-14">
            <div class="pb-6 sm:pb-0">
                <p class="font-serif text-2xl font-semibold tracking-[0.3em]">PACH</p>
                <p class="mt-3 text-sm leading-relaxed text-neutral-600">{{ config('shop.tagline') }}</p>
                <div class="mt-5 flex gap-2">
                    <a href="{{ config('shop.contact.instagram') }}" target="_blank" rel="noopener" class="flex size-10 items-center justify-center rounded-full bg-white hover:bg-sand" aria-label="Instagram">
                        <svg class="size-5" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41a3.72 3.72 0 0 1-1.38-.9 3.72 3.72 0 0 1-.9-1.38c-.16-.42-.36-1.06-.41-2.23C2.17 15.58 2.16 15.2 2.16 12s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41C8.42 2.17 8.8 2.16 12 2.16M12 0C8.74 0 8.33.01 7.05.07 5.78.13 4.9.33 4.14.63a5.88 5.88 0 0 0-2.13 1.38A5.88 5.88 0 0 0 .63 4.14C.33 4.9.13 5.78.07 7.05.01 8.33 0 8.74 0 12s.01 3.67.07 4.95c.06 1.27.26 2.15.56 2.91.3.79.72 1.46 1.38 2.13a5.88 5.88 0 0 0 2.13 1.38c.76.3 1.64.5 2.91.56C8.33 23.99 8.74 24 12 24s3.67-.01 4.95-.07c1.27-.06 2.15-.26 2.91-.56a5.88 5.88 0 0 0 2.13-1.38 5.88 5.88 0 0 0 1.38-2.13c.3-.76.5-1.64.56-2.91.06-1.28.07-1.69.07-4.95s-.01-3.67-.07-4.95c-.06-1.27-.26-2.15-.56-2.91a5.88 5.88 0 0 0-1.38-2.13A5.88 5.88 0 0 0 19.86.63C19.1.33 18.22.13 16.95.07 15.67.01 15.26 0 12 0Zm0 5.84a6.16 6.16 0 1 0 0 12.32 6.16 6.16 0 0 0 0-12.32ZM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8Zm6.4-11.85a1.44 1.44 0 1 0 0 2.88 1.44 1.44 0 0 0 0-2.88Z"/></svg>
                    </a>
                    <a href="https://wa.me/{{ config('shop.contact.whatsapp') }}" target="_blank" rel="noopener" class="flex size-10 items-center justify-center rounded-full bg-white hover:bg-sand" aria-label="WhatsApp">
                        <x-heroicon-o-chat-bubble-left-right class="size-5" />
                    </a>
                    <a href="mailto:{{ config('shop.contact.email') }}" class="flex size-10 items-center justify-center rounded-full bg-white hover:bg-sand" aria-label="Email">
                        <x-heroicon-o-envelope class="size-5" />
                    </a>
                </div>
            </div>

            @foreach ([
                'Shop' => collect([['All products', route('shop')]])
                    ->merge($navCategories->map(fn ($c) => [$c->name, route('category', $c)]))->all(),
                'Help' => $footerPages->map(fn ($p) => [$p->title, route('page', $p)])->all(),
            ] as $heading => $links)
                <div x-data="{ open: false }" class="border-t border-sand sm:border-0">
                    <button type="button" @click="open = !open" class="flex w-full items-center justify-between py-4 text-xs font-medium tracking-[0.2em] uppercase sm:pointer-events-none sm:py-0" :aria-expanded="open">
                        {{ $heading }}
                        <x-heroicon-o-chevron-down class="size-4 transition sm:hidden" ::class="open && 'rotate-180'" />
                    </button>
                    <ul :class="open ? 'block' : 'hidden'" class="space-y-3 pb-5 text-sm text-neutral-600 sm:mt-4 sm:block! sm:pb-0">
                        @foreach ($links as [$label, $url])
                            <li><a href="{{ $url }}" wire:navigate class="hover:text-ink">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endforeach

            <div class="border-t border-sand pt-5 sm:border-0 sm:pt-0">
                <p class="text-xs font-medium tracking-[0.2em] uppercase">Visit our store</p>
                <a href="{{ config('shop.contact.store_map') }}" target="_blank" rel="noopener" class="mt-4 flex gap-2 text-sm leading-relaxed text-neutral-600 hover:text-ink">
                    <x-heroicon-o-map-pin class="mt-0.5 size-4 shrink-0" />
                    {{ config('shop.contact.store') }}
                </a>
                <a href="tel:{{ preg_replace('/\s+/', '', config('shop.contact.phone')) }}" class="mt-3 flex items-center gap-2 text-sm text-neutral-600 hover:text-ink">
                    <x-heroicon-o-phone class="size-4" /> {{ config('shop.contact.phone') }}
                </a>
                <a href="mailto:{{ config('shop.contact.email') }}" class="mt-3 flex items-center gap-2 text-sm text-neutral-600 hover:text-ink">
                    <x-heroicon-o-envelope class="size-4" /> {{ config('shop.contact.email') }}
                </a>
            </div>
        </div>
        <div class="flex flex-col items-center gap-3 border-t border-sand py-5 text-center text-xs text-neutral-500">
            <button type="button" data-pwa-install class="items-center gap-2 border border-ink px-4 py-2 text-[11px] tracking-[0.15em] text-ink uppercase hover:bg-ink hover:text-white">
                <x-heroicon-o-device-phone-mobile class="size-4" /> Install the app
            </button>
            <span>&copy; {{ date('Y') }} {{ config('shop.name') }}. All rights reserved.</span>
        </div>
    </footer>

    {{-- WhatsApp: floating on desktop, above the tab bar on phones --}}
    <a href="https://wa.me/{{ config('shop.contact.whatsapp') }}" target="_blank" rel="noopener"
       @class([
           'fixed right-4 z-30 size-12 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lg transition hover:scale-105',
           'bottom-20 flex lg:bottom-5' => $showTabBar && ! request()->routeIs('me'),
           'bottom-5 hidden lg:flex' => ! $showTabBar || request()->routeIs('me'),
       ])
       aria-label="Chat on WhatsApp">
        <svg class="size-6" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.47 14.38c-.3-.15-1.75-.86-2.02-.96-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.64.07-.3-.15-1.25-.46-2.38-1.47-.88-.79-1.47-1.76-1.65-2.06-.17-.3-.02-.46.13-.6.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.61-.92-2.2-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.88 1.21 3.08.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.09 1.75-.72 2-1.41.25-.69.25-1.29.17-1.41-.07-.12-.27-.2-.57-.35M12.05 21.79h-.01a9.87 9.87 0 0 1-5.03-1.38l-.36-.21-3.74.98 1-3.65-.24-.37a9.86 9.86 0 0 1-1.51-5.26c0-5.45 4.44-9.88 9.89-9.88 2.64 0 5.12 1.03 6.99 2.9a9.82 9.82 0 0 1 2.89 6.99c0 5.45-4.43 9.88-9.88 9.88m8.41-18.3A11.81 11.81 0 0 0 12.05 0C5.5 0 .16 5.34.16 11.89c0 2.1.55 4.14 1.59 5.95L.06 24l6.3-1.65a11.88 11.88 0 0 0 5.68 1.45h.01c6.55 0 11.89-5.34 11.89-11.89 0-3.18-1.24-6.16-3.48-8.41"/></svg>
    </a>

    {{-- Mobile tab bar --}}
    @if ($showTabBar)
        <nav x-data class="fixed inset-x-0 bottom-0 z-40 border-t border-sand bg-white/95 backdrop-blur lg:hidden" style="padding-bottom: env(safe-area-inset-bottom)" aria-label="Main">
            <div class="flex h-16">
                @foreach ([
                    ['home', 'Home', route('home'), request()->routeIs('home')],
                    ['squares-2x2', 'Shop', route('shop'), request()->routeIs('shop', 'category')],
                ] as [$icon, $label, $url, $active])
                    <a href="{{ $url }}" wire:navigate @class(['flex flex-1 flex-col items-center justify-center gap-0.5 text-[10px] tracking-wide uppercase', 'text-ink' => $active, 'text-neutral-500' => ! $active])>
                        <x-dynamic-component :component="($active ? 'heroicon-s-' : 'heroicon-o-').$icon" class="size-6" />
                        {{ $label }}
                    </a>
                @endforeach
                <button type="button" @click="$dispatch('open-search')" class="flex flex-1 flex-col items-center justify-center gap-0.5 text-[10px] tracking-wide text-neutral-500 uppercase">
                    <x-heroicon-o-magnifying-glass class="size-6" />
                    Search
                </button>
                <livewire:cart-count variant="tab" />
                @php($accountActive = request()->routeIs('me', 'account.*', 'login', 'register', 'page'))
                <a href="{{ route('me') }}" wire:navigate
                   @class(['flex flex-1 flex-col items-center justify-center gap-0.5 text-[10px] tracking-wide uppercase', 'text-ink' => $accountActive, 'text-neutral-500' => ! $accountActive])>
                    <x-dynamic-component :component="$accountActive ? 'heroicon-s-user' : 'heroicon-o-user'" class="size-6" />
                    Me
                </a>
            </div>
        </nav>
    @endif

    {{-- Cart drawer and search: Alpine owns open/close, Livewire only renders the contents --}}
    <div x-data="{ open: false }" x-on:open-cart.window="open = true" x-on:keydown.escape.window="open = false"
         x-effect="document.documentElement.classList.toggle('overflow-hidden', open)">
        <div x-cloak x-show="open" class="fixed inset-0 z-[60]" role="dialog" aria-modal="true" aria-label="Your cart">
            <div x-show="open" x-transition.opacity class="absolute inset-0 bg-black/40" @click="open = false"></div>
            <div x-show="open"
                 x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0"
                 x-transition:leave="transition duration-200 ease-in" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full"
                 class="absolute inset-y-0 right-0 w-full max-w-md bg-white shadow-2xl">
                <livewire:cart-drawer />
            </div>
        </div>
    </div>

    <div x-data="{ open: false }" x-on:open-search.window="open = true; $nextTick(() => document.getElementById('site-search')?.focus())"
         x-on:keydown.escape.window="open = false" x-effect="document.documentElement.classList.toggle('overflow-hidden', open)">
        <div x-cloak x-show="open" x-transition.opacity class="fixed inset-0 z-[60] overflow-y-auto bg-white" role="dialog" aria-modal="true" aria-label="Search">
            <livewire:search-overlay />
        </div>
    </div>

    {{-- Toasts: session flashes plus "notify" browser events --}}
    <div x-data="{
            toasts: [],
            add(message, type = 'success') {
                const id = Date.now() + Math.random();
                this.toasts.push({ id, message, type });
                setTimeout(() => this.toasts = this.toasts.filter(t => t.id !== id), 3500);
            },
         }"
         x-init="
            @if (session('status')) add(@js(session('status'))); @endif
            @if (session('error')) add(@js(session('error')), 'error'); @endif
         "
         x-on:notify.window="add($event.detail.message ?? $event.detail[0]?.message, $event.detail.type ?? $event.detail[0]?.type)"
         class="pointer-events-none fixed inset-x-0 top-20 z-[70] flex flex-col items-center gap-2 px-4">
        <template x-for="toast in toasts" :key="toast.id">
            <div x-transition class="pointer-events-auto flex w-full max-w-sm animate-slide-up items-start gap-3 px-4 py-3 text-sm text-white shadow-lg"
                 :class="toast.type === 'error' ? 'bg-sale' : 'bg-ink'">
                <template x-if="toast.type === 'error'"><x-heroicon-o-exclamation-circle class="mt-0.5 size-5 shrink-0" /></template>
                <template x-if="toast.type !== 'error'"><x-heroicon-o-check-circle class="mt-0.5 size-5 shrink-0" /></template>
                <p x-text="toast.message"></p>
            </div>
        </template>
    </div>

    @stack('scripts')
</body>
</html>
