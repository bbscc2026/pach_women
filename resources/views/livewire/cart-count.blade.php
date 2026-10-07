@if ($variant === 'tab')
    <button type="button" @click="$dispatch('open-cart')" class="flex flex-1 flex-col items-center justify-center gap-0.5 text-[10px] tracking-wide text-neutral-500 uppercase" aria-label="Cart ({{ $count }} items)">
        <span class="relative">
            <x-heroicon-o-shopping-bag class="size-6" />
            @if ($count > 0)
                <span wire:key="badge-{{ $count }}" class="absolute -top-1.5 -right-2 flex h-4 min-w-4 animate-slide-up items-center justify-center rounded-full bg-ink px-1 text-[10px] font-medium text-white">{{ $count }}</span>
            @endif
        </span>
        Cart
    </button>
@else
    <button type="button" @click="$dispatch('open-cart')" class="relative flex size-11 items-center justify-center" aria-label="Cart ({{ $count }} items)">
        <x-heroicon-o-shopping-bag class="size-6" />
        @if ($count > 0)
            <span wire:key="badge-{{ $count }}" class="absolute top-1 right-0.5 flex h-4.5 min-w-4.5 animate-slide-up items-center justify-center rounded-full bg-ink px-1 text-[10px] font-medium text-white">{{ $count }}</span>
        @endif
    </button>
@endif
