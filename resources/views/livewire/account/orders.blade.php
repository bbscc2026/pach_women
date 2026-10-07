<x-account-layout heading="My orders">
    @if ($orders->isEmpty())
        <div class="bg-cream px-6 py-12 text-center">
            <x-heroicon-o-shopping-bag class="mx-auto size-10 text-neutral-400" />
            <p class="mt-3 font-serif text-xl">No orders yet</p>
            <a href="{{ route('shop') }}" wire:navigate class="btn-dark mt-6">Start shopping</a>
        </div>
    @else
        <ul class="divide-y divide-sand border-y border-sand">
            @foreach ($orders as $order)
                <li wire:key="order-{{ $order->id }}">
                    <a href="{{ route('account.orders.show', $order) }}" wire:navigate class="flex flex-wrap items-center justify-between gap-3 py-4 hover:bg-cream/60 sm:px-2">
                        <div>
                            <p class="font-medium">{{ $order->number }}</p>
                            <p class="text-xs text-neutral-500">{{ $order->created_at->format('d M Y') }} &middot; {{ $order->items_count }} {{ Str::plural('item', $order->items_count) }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <x-order-status :order="$order" />
                            <span class="w-20 text-right font-medium">{{ inr($order->total) }}</span>
                            <x-heroicon-o-chevron-right class="size-4 text-neutral-400" />
                        </div>
                    </a>
                </li>
            @endforeach
        </ul>
        <div class="mt-6">{{ $orders->links() }}</div>
    @endif
</x-account-layout>
