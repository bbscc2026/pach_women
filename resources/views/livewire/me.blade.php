<div class="min-h-[70vh] bg-cream/60 pb-10">
    <div class="container-shop max-w-2xl pt-5">
        {{-- Profile header --}}
        <div class="flex items-center gap-4 rounded-2xl bg-ink p-5 text-white">
            <div class="flex size-14 shrink-0 items-center justify-center rounded-full bg-white/10 font-serif text-2xl">
                @if ($user)
                    {{ Str::upper(Str::substr($user->name, 0, 1)) }}
                @else
                    <x-heroicon-o-user class="size-7" />
                @endif
            </div>
            <div class="min-w-0 flex-1">
                @if ($user)
                    <p class="truncate font-serif text-2xl">{{ $user->name }}</p>
                    <p class="truncate text-sm text-white/60">{{ $user->email }}</p>
                @else
                    <p class="font-serif text-2xl">Welcome to PACH</p>
                    <p class="text-sm text-white/60">Log in to track orders and check out faster.</p>
                @endif
            </div>
        </div>

        @guest
            <div class="mt-4 grid grid-cols-2 gap-3">
                <a href="{{ route('login') }}" wire:navigate class="btn-dark rounded-xl">Log in</a>
                <a href="{{ route('register') }}" wire:navigate class="btn-light rounded-xl">Sign up</a>
            </div>
        @endguest

        @auth
            <x-app-list heading="My account">
                <x-app-list-item :href="route('account.orders')" icon="clipboard-document-list" label="My orders" :badge="$openOrders ?: null" :detail="$openOrders ? $openOrders.' in progress' : null" />
                <x-app-list-item :href="route('account.addresses')" icon="map-pin" label="Addresses" />
                <x-app-list-item :href="route('account.profile')" icon="user" label="Profile & password" />
            </x-app-list>
        @endauth

        <x-app-list heading="Shop">
            <x-app-list-item :href="route('shop')" icon="squares-2x2" label="All products" />
            <x-app-list-item :href="route('shop', ['sort' => 'sale'])" icon="tag" label="Offers & sale" />
            <x-app-list-item x-data x-on:click="$dispatch('open-cart')" icon="shopping-bag" label="My cart" />
        </x-app-list>

        <x-app-list heading="Contact us">
            <x-app-list-item :href="'https://wa.me/'.config('shop.contact.whatsapp')" external icon="chat-bubble-left-right" label="WhatsApp" detail="Usually replies within a few hours" />
            <x-app-list-item :href="'tel:'.preg_replace('/\s+/', '', config('shop.contact.phone'))" external icon="phone" label="Call" :detail="config('shop.contact.phone')" />
            <x-app-list-item :href="'mailto:'.config('shop.contact.email')" external icon="envelope" label="Email" :detail="config('shop.contact.email')" />
            <x-app-list-item :href="config('shop.contact.instagram')" external icon="camera" label="Instagram" detail="@pach_women" />
            <x-app-list-item :href="config('shop.contact.store_map')" external icon="building-storefront" label="Visit our store" :detail="config('shop.contact.store')" />
        </x-app-list>

        <x-app-list heading="Help">
            @foreach ($pages as $page)
                <x-app-list-item :href="route('page', $page)" icon="document-text" :label="$page->title" />
            @endforeach
        </x-app-list>

        {{-- Shown only when the phone can install the app (handled by /pwa.js) --}}
        <div data-pwa-install class="mt-6 w-full">
            <x-app-list class="mt-0 w-full">
                <x-app-list-item icon="device-phone-mobile" label="Install the PACH app" detail="Opens full-screen, works offline" />
            </x-app-list>
        </div>
        <p data-pwa-ios-hint class="mt-6 rounded-2xl border border-sand bg-white p-4 text-sm text-neutral-600">
            <strong class="text-ink">Install the app:</strong> tap <strong>Share</strong>
            <x-heroicon-o-arrow-up-on-square class="inline size-4 align-text-bottom" /> then <strong>Add to Home Screen</strong>.
        </p>

        @auth
            <form method="POST" action="{{ route('logout') }}" class="mt-6">
                @csrf
                <x-app-list class="mt-0">
                    <x-app-list-item icon="arrow-right-start-on-rectangle" label="Log out" danger onclick="this.closest('form').submit()" />
                </x-app-list>
            </form>
        @endauth

        <p class="mt-8 text-center text-xs text-neutral-400">&copy; {{ date('Y') }} {{ config('shop.name') }}</p>
    </div>
</div>
