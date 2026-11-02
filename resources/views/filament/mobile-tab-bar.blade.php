{{-- Admin bottom tab bar on phones/tablets (hidden on desktop). "More" opens Filament's sidebar. --}}
@use('App\Filament\Resources\Orders\OrderResource')
@use('App\Filament\Resources\Products\ProductResource')
@use('App\Models\Order')

@php
    $toShip = Order::where('status', 'confirmed')->count();
    $productsActive = request()->routeIs('filament.admin.resources.products.*') && ! request()->routeIs('filament.admin.resources.products.create');
    $tabs = [
        ['Home', 'home', url('/admin'), request()->routeIs('filament.admin.pages.dashboard'), null],
        ['Orders', 'clipboard-document-list', OrderResource::getUrl(), request()->routeIs('filament.admin.resources.orders.*') && ! request()->routeIs('filament.admin.resources.orders.create'), $toShip],
    ];
@endphp

<nav class="pw-tabbar" aria-label="Admin" x-data="{ createOpen: false }" x-on:keydown.escape.window="if (createOpen) { createOpen = false; $refs.createButton.focus() }">
    @foreach ($tabs as [$label, $icon, $url, $active, $badge])
        <a href="{{ $url }}" wire:navigate @class(['pw-tab', 'is-active' => $active])>
            <x-filament::icon :icon="($active ? 'heroicon-s-' : 'heroicon-o-').$icon" />
            {{ $label }}
            @if ($badge)
                <span class="pw-tab-badge" aria-label="{{ $badge }} to ship">{{ $badge > 99 ? '99+' : $badge }}</span>
            @endif
        </a>
    @endforeach

    <button type="button" x-ref="createButton" x-on:click="createOpen = !createOpen; if (createOpen) $nextTick(() => $refs.newOrder.focus())" x-bind:aria-expanded="createOpen" aria-controls="pw-create-options" @class(['pw-tab', 'pw-tab-add', 'is-active' => request()->routeIs('filament.admin.resources.orders.create', 'filament.admin.resources.products.create')]) aria-label="Create">
        <span class="pw-plus"><x-filament::icon icon="heroicon-o-plus" /></span>
        Create
    </button>
    <div id="pw-create-options" class="pw-create-panel" x-show="createOpen" x-cloak x-on:click.outside="createOpen = false" role="group" aria-label="Create options">
        <div class="pw-create-heading">
            <strong>What would you like to create?</strong>
            <button type="button" aria-label="Close create options" x-on:click="createOpen = false; $refs.createButton.focus()">✕</button>
        </div>
        <a x-ref="newOrder" href="{{ OrderResource::getUrl('create') }}" wire:navigate x-on:click="createOpen = false">
            <x-filament::icon icon="heroicon-o-shopping-bag" />
            <span><strong>New order / POS</strong><small>WhatsApp, Instagram or in-store order</small></span>
            <x-filament::icon icon="heroicon-o-chevron-right" />
        </a>
        <a href="{{ ProductResource::getUrl('create') }}" wire:navigate x-on:click="createOpen = false">
            <x-filament::icon icon="heroicon-o-plus-circle" />
            <span><strong>New product</strong><small>Add photos, price, sizes and stock</small></span>
            <x-filament::icon icon="heroicon-o-chevron-right" />
        </a>
    </div>

    <a href="{{ ProductResource::getUrl() }}" wire:navigate @class(['pw-tab', 'is-active' => $productsActive])>
        <x-filament::icon :icon="($productsActive ? 'heroicon-s-' : 'heroicon-o-').'tag'" />
        Products
    </a>

    <button type="button" class="pw-tab" x-data x-on:click="$store.sidebar.open()" aria-label="More: all admin sections">
        <x-filament::icon icon="heroicon-o-bars-3" />
        More
    </button>
</nav>
