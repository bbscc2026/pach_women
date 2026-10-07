<x-account-layout :heading="'Order '.$order->number">
    <p class="flex items-center gap-2 text-sm text-neutral-600">
        <x-heroicon-o-calendar class="size-4" /> Placed on {{ $order->created_at->format('d M Y, h:i A') }}
    </p>

    <x-order-tracker :order="$order" class="mt-6" />

    @if ($order->tracking_number)
        <div class="mt-5 flex items-center justify-between gap-3 bg-cream px-4 py-3 text-sm" x-data>
            <span class="flex items-center gap-2"><x-heroicon-o-truck class="size-5" /> Tracking no. <strong>{{ $order->tracking_number }}</strong></span>
            <button type="button" @click="navigator.clipboard.writeText(@js($order->tracking_number)).then(() => $dispatch('notify', { message: 'Tracking number copied' }))"
                    class="flex size-9 items-center justify-center hover:bg-sand" aria-label="Copy tracking number">
                <x-heroicon-o-clipboard-document class="size-5" />
            </button>
        </div>
    @endif

    <ul class="mt-6 divide-y divide-sand border-y border-sand">
        @foreach ($order->items as $item)
            <li class="flex items-center gap-4 py-4 text-sm">
                <div class="w-16 shrink-0">
                    <div class="aspect-[3/4] overflow-hidden bg-cream">
                        @if ($item->product?->thumbnailUrl())
                            <img src="{{ $item->product->thumbnailUrl() }}" alt="" class="size-full object-cover">
                        @endif
                    </div>
                </div>
                <div class="flex-1">
                    @if ($item->product?->isVisible())
                        <a href="{{ route('product', $item->product) }}" wire:navigate class="hover:underline">{{ $item->name }}</a>
                    @else
                        <p>{{ $item->name }}</p>
                    @endif
                    <p class="text-xs text-neutral-500">@if ($item->size)Size {{ $item->size }} &middot; @endif Qty {{ $item->quantity }} &middot; {{ inr($item->price) }}</p>
                </div>
                <p class="font-medium">{{ inr($item->lineTotal()) }}</p>
            </li>
        @endforeach
    </ul>

    <div class="mt-6 grid gap-6 sm:grid-cols-2">
        <div class="bg-cream p-5 text-sm">
            <p class="label">Delivery address</p>
            <p>{{ $order->name }} &middot; {{ $order->phone }}<br>{{ $order->address() }}</p>
            <p class="label mt-4">Payment</p>
            <p>{{ $order->paymentMethodLabel() }} &middot; {{ $order->paymentLabel() }}</p>
        </div>
        <div class="bg-cream p-5">
            <x-order-summary :subtotal="$order->subtotal" :shipping="$order->shipping" :total="$order->total" />
        </div>
    </div>

    <a href="{{ route('account.orders') }}" wire:navigate class="mt-8 inline-block text-sm underline underline-offset-4">&larr; All orders</a>
</x-account-layout>
