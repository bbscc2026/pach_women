{{-- Admin bottom tab bar on phones/tablets (hidden on desktop). "More" opens Filament's sidebar. --}}
@use('App\Filament\Resources\Orders\OrderResource')
@use('App\Filament\Resources\Products\ProductResource')
@use('App\Models\Order')

@php
    $toShip = Order::where('status', 'confirmed')->count();
    $productsActive = request()->routeIs('filament.admin.resources.products.*') && ! request()->routeIs('filament.admin.resources.products.create');
    $tabs = [
        ['Home', 'home', url('/admin'), request()->routeIs('filament.admin.pages.dashboard'), null],
        ['Orders', 'clipboard-document-list', OrderResource::getUrl(), request()->routeIs('filament.admin.resources.orders.*'), $toShip],
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

    <a href="{{ ProductResource::getUrl('create') }}" wire:navigate class="pw-tab pw-tab-add" aria-label="New product">
        <span class="pw-plus"><x-filament::icon icon="heroicon-o-plus" /></span>
        New
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
