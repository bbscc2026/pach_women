{{-- One row in an <x-app-list>: icon, label, optional detail/badge, chevron. --}}
@props(['href' => null, 'icon', 'label', 'detail' => null, 'badge' => null, 'external' => false, 'navigate' => true, 'danger' => false])

<li>
    @php($classes = 'flex min-h-14 w-full items-center gap-3.5 px-4 py-3 text-left transition active:bg-cream')
    @if ($href)
        <a href="{{ $href }}" @if ($external) target="_blank" rel="noopener" @elseif ($navigate) wire:navigate @endif {{ $attributes->class($classes) }}>
    @else
        <button type="button" {{ $attributes->class($classes) }}>
    @endif
        <span @class(['flex size-9 shrink-0 items-center justify-center rounded-xl', 'bg-cream text-ink' => ! $danger, 'bg-red-50 text-sale' => $danger])>
            <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-5" />
        </span>
        <span class="min-w-0 flex-1">
            <span @class(['block truncate text-[15px]', 'text-sale' => $danger])>{{ $label }}</span>
            @if ($detail)
                <span class="block truncate text-xs text-neutral-500">{{ $detail }}</span>
            @endif
        </span>
        @if ($badge)
            <span class="rounded-full bg-ink px-2 py-0.5 text-[11px] font-medium text-white">{{ $badge }}</span>
        @endif
        @unless ($danger)
            <x-dynamic-component :component="$external ? 'heroicon-o-arrow-up-right' : 'heroicon-o-chevron-right'" class="size-4 shrink-0 text-neutral-400" />
        @endunless
    @if ($href)
        </a>
    @else
        </button>
    @endif
</li>
