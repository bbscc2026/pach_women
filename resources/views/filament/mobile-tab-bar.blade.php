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

<nav class="pw-tabbar" aria-label="Admin">
    @foreach ($tabs as [$label, $icon, $url, $active, $badge])
        <a href="{{ $url }}" wire:navigate @class(['pw-tab', 'is-active' => $active])>
            <x-filament::icon :icon="($active ? 'heroicon-s-' : 'heroicon-o-').$icon" />
            {{ $label }}
            @if ($badge)
                <span class="pw-tab-badge" aria-label="{{ $badge }} to ship">{{ $badge > 99 ? '99+' : $badge }}</span>
            @endif
        </a>
    @endforeach

    <a href="{{ OrderResource::getUrl('create') }}" wire:navigate @class(['pw-tab', 'pw-tab-add', 'is-active' => request()->routeIs('filament.admin.resources.orders.create')]) aria-label="Create order">
        <span class="pw-plus"><x-filament::icon icon="heroicon-o-plus" /></span>
        New order
    </a>

    <a href="{{ ProductResource::getUrl() }}" wire:navigate @class(['pw-tab', 'is-active' => $productsActive])>
        <x-filament::icon :icon="($productsActive ? 'heroicon-s-' : 'heroicon-o-').'tag'" />
        Products
    </a>

    <button type="button" class="pw-tab" x-data x-on:click="$store.sidebar.open()" aria-label="More: all admin sections">
        <x-filament::icon icon="heroicon-o-bars-3" />
        More
    </button>
</nav>
