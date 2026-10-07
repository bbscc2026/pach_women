@props(['heading'])

<div class="container-shop py-6 sm:py-10">
    <div class="flex items-center gap-4">
        <div class="flex size-14 shrink-0 items-center justify-center rounded-full bg-ink font-serif text-2xl text-white">
            {{ Str::upper(Str::substr(auth()->user()->name, 0, 1)) }}
        </div>
        <div class="min-w-0">
            <p class="text-sm text-neutral-500">Hello,</p>
            <p class="truncate font-serif text-2xl font-semibold">{{ auth()->user()->name }}</p>
        </div>
    </div>

    <div class="mt-6 grid gap-8 lg:mt-10 lg:grid-cols-4 lg:gap-10">
        <nav class="scroll-row -mx-4 gap-2 px-4 text-sm lg:mx-0 lg:flex-col lg:gap-1 lg:px-0" aria-label="Account">
            @foreach ([
                'account.orders' => ['clipboard-document-list', 'My orders'],
                'account.addresses' => ['map-pin', 'Addresses'],
                'account.profile' => ['user', 'Profile & password'],
            ] as $routeName => [$icon, $label])
                @php($active = request()->routeIs($routeName.'*'))
                <a href="{{ route($routeName) }}" wire:navigate
                   @class(['flex min-h-11 shrink-0 items-center gap-2 px-4', 'bg-ink text-white' => $active, 'bg-cream hover:bg-sand' => ! $active])>
                    <x-dynamic-component :component="'heroicon-o-'.$icon" class="size-5" /> {{ $label }}
                </a>
            @endforeach
            <form method="POST" action="{{ route('logout') }}" class="shrink-0">
                @csrf
                <button type="submit" class="flex min-h-11 w-full items-center gap-2 px-4 text-left text-neutral-600 hover:text-ink">
                    <x-heroicon-o-arrow-right-start-on-rectangle class="size-5" /> Log out
                </button>
            </form>
        </nav>

        <div class="lg:col-span-3">
            <h2 class="font-serif text-2xl font-semibold">{{ $heading }}</h2>
            <div class="mt-5">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
