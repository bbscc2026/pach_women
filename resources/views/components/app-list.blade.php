{{-- Grouped, native-style list for app screens. Use <x-app-list-item> inside. --}}
@props(['heading' => null])

<section {{ $attributes->class('mt-6') }}>
    @if ($heading)
        <p class="mb-2 px-1 text-[11px] font-medium tracking-[0.15em] text-neutral-500 uppercase">{{ $heading }}</p>
    @endif
    <ul class="divide-y divide-sand overflow-hidden rounded-2xl border border-sand bg-white">
        {{ $slot }}
    </ul>
</section>
