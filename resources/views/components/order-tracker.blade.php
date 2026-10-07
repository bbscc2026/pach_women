@props(['order'])

@php
    $steps = [
        'pending' => ['clock', 'Placed'],
        'confirmed' => ['check-badge', 'Confirmed'],
        'shipped' => ['truck', 'Shipped'],
        'delivered' => ['home', 'Delivered'],
    ];
    $current = array_search($order->status, array_keys($steps), true);
@endphp

@if ($order->status === 'cancelled')
    <p {{ $attributes->class('flex items-center gap-2 bg-neutral-100 px-4 py-3 text-sm') }}>
        <x-heroicon-o-x-circle class="size-5" /> This order was cancelled.
    </p>
@else
    <ol {{ $attributes->class('grid grid-cols-4') }}>
        @foreach ($steps as $status => [$icon, $label])
            @php($done = $loop->index <= $current)
            <li class="relative flex flex-col items-center text-center">
                @unless ($loop->first)
                    <span @class(['absolute top-5 right-1/2 h-0.5 w-full -translate-y-1/2', 'bg-ink' => $done, 'bg-sand' => ! $done])></span>
                @endunless
                <span @class(['relative flex size-10 items-center justify-center rounded-full', 'bg-ink text-white' => $done, 'bg-sand text-neutral-500' => ! $done])>
                    <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-5" />
                </span>
                <span @class(['mt-2 text-[11px] tracking-wide uppercase', 'text-ink font-medium' => $done, 'text-neutral-500' => ! $done])>{{ $label }}</span>
            </li>
        @endforeach
    </ol>
@endif
