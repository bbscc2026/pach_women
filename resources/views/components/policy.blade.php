@props(['title', 'updated' => null])

<x-layouts::app :title="$title">
    <article class="container-shop max-w-3xl py-10 sm:py-14">
        <h1 class="text-4xl font-semibold sm:text-5xl" data-reveal>{{ $title }}</h1>
        @if ($updated)
            <p class="mt-2 text-xs tracking-wide text-neutral-500 uppercase">Last updated {{ $updated }}</p>
        @endif
        <div class="rich-content mt-8" data-reveal>
            {{ $slot }}
        </div>
    </article>
</x-layouts::app>
