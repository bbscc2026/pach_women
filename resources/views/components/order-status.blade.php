@props(['order'])

@php
    $colors = [
        'pending' => 'bg-amber-100 text-amber-800',
        'confirmed' => 'bg-blue-100 text-blue-800',
        'shipped' => 'bg-indigo-100 text-indigo-800',
        'delivered' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-neutral-200 text-neutral-700',
    ];
@endphp

<span {{ $attributes->class(['inline-block px-2 py-0.5 text-xs font-medium', $colors[$order->status] ?? 'bg-neutral-100']) }}>
    {{ $order->statusLabel() }}
</span>
