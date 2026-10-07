<x-layouts::app title="Order placed">
    <div class="container-shop max-w-2xl py-10 text-center sm:py-16">
        <div class="mx-auto flex size-14 items-center justify-center rounded-full bg-ink text-white">
            <svg class="size-7" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
        </div>
        <h1 class="mt-6 text-4xl font-semibold">Thank you, {{ Str::before($order->name, ' ') }}!</h1>
        <p class="mt-3 text-neutral-600">Your order <strong>{{ $order->number }}</strong> has been placed. We'll message you on {{ $order->phone }} when it ships.</p>

        <a href="https://wa.me/{{ config('shop.contact.whatsapp') }}?text={{ rawurlencode('Hi PACH, I just placed order '.$order->number) }}" target="_blank" rel="noopener"
           class="mt-5 inline-flex items-center gap-2 text-sm underline underline-offset-4">
            <x-heroicon-o-chat-bubble-left-right class="size-4" /> Questions? Message us on WhatsApp
        </a>

        <div class="mt-8 bg-cream p-6 text-left">
            <ul class="space-y-3 text-sm">
                @foreach ($order->items as $item)
                    <li class="flex justify-between gap-4">
                        <span>{{ $item->name }} @if ($item->size)({{ $item->size }})@endif &times; {{ $item->quantity }}</span>
                        <span>{{ inr($item->lineTotal()) }}</span>
                    </li>
                @endforeach
            </ul>
            <div class="mt-5 border-t border-sand pt-5">
                <x-order-summary :subtotal="$order->subtotal" :shipping="$order->shipping" :total="$order->total" />
            </div>
            <div class="mt-5 grid gap-4 border-t border-sand pt-5 text-sm sm:grid-cols-2">
                <div>
                    <p class="label">Deliver to</p>
                    <p>{{ $order->name }}<br>{{ $order->address() }}</p>
                </div>
                <div>
                    <p class="label">Payment</p>
                    <p>{{ $order->paymentMethodLabel() }}<br>{{ $order->paymentLabel() }}</p>
                </div>
            </div>
        </div>

        <div class="mt-8 flex flex-col justify-center gap-3 sm:flex-row">
            <a href="{{ route('account.orders.show', $order) }}" wire:navigate class="btn-light"><x-heroicon-o-clipboard-document-list class="size-5" /> View order</a>
            <a href="{{ route('shop') }}" wire:navigate class="btn-dark">Continue shopping <x-heroicon-o-arrow-right class="size-4" /></a>
        </div>
    </div>
</x-layouts::app>
