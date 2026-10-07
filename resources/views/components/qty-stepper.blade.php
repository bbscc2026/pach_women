@props(['quantity', 'decrease', 'increase', 'max' => null, 'size' => 'md'])

@php($pad = $size === 'sm' ? 'size-9' : 'size-11')

<div {{ $attributes->class('inline-flex items-center border border-sand') }}>
    <button type="button" wire:click="{{ $decrease }}" class="{{ $pad }} flex items-center justify-center hover:bg-cream" aria-label="Decrease quantity">
        @if ($quantity <= 1 && $size === 'sm')
            <x-heroicon-o-trash class="size-4" />
        @else
            <x-heroicon-o-minus class="size-4" />
        @endif
    </button>
    <span class="w-8 text-center text-sm tabular-nums">{{ $quantity }}</span>
    <button type="button" wire:click="{{ $increase }}" @disabled($max !== null && $quantity >= $max)
            class="{{ $pad }} flex items-center justify-center hover:bg-cream disabled:opacity-30" aria-label="Increase quantity">
        <x-heroicon-o-plus class="size-4" />
    </button>
</div>
