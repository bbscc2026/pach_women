{{-- Password field with a show/hide toggle. Pass id, wire:model and autocomplete as attributes. --}}
<div x-data="{ show: false }" class="relative">
    <input wire:ignore.self :type="show ? 'text' : 'password'" type="password" {{ $attributes->class('input pr-12') }}>
    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex w-12 items-center justify-center text-neutral-500 hover:text-ink"
            :aria-label="show ? 'Hide password' : 'Show password'">
        <span wire:ignore.self x-show="!show"><x-heroicon-o-eye class="size-5" /></span>
        <span wire:ignore.self x-show="show" x-cloak><x-heroicon-o-eye-slash class="size-5" /></span>
    </button>
</div>
