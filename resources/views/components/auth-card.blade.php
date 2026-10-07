@props(['title', 'subtitle' => null])

<div class="container-shop flex justify-center py-16">
    <div class="w-full max-w-md">
        <h1 class="text-center text-4xl font-semibold">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-3 text-center text-sm text-neutral-600">{{ $subtitle }}</p>
        @endif
        <div class="mt-8">
            {{ $slot }}
        </div>
    </div>
</div>
