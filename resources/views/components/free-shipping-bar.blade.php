@props(['gap', 'progress'])

<div {{ $attributes->class('text-sm') }}>
    <p class="flex items-center gap-2">
        <x-heroicon-o-truck class="size-5 shrink-0 text-clay" />
        @if ($gap > 0)
            <span>You're <strong>{{ inr($gap) }}</strong> away from free shipping</span>
        @else
            <span>You've unlocked <strong>free shipping</strong></span>
        @endif
    </p>
    <div class="mt-2 h-1 overflow-hidden rounded-full bg-sand">
        <div class="h-full rounded-full bg-ink transition-all duration-500" style="width: {{ $progress }}%"></div>
    </div>
</div>
