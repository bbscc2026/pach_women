<x-account-layout heading="Addresses">
    @if ($editing)
        <form wire:submit="save" class="grid max-w-2xl gap-4 sm:grid-cols-2">
            <div>
                <label for="name" class="label">Full name</label>
                <input id="name" wire:model="name" class="input">
                @error('name') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="phone" class="label">Mobile number</label>
                <input id="phone" type="tel" wire:model="phone" class="input">
                @error('phone') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label for="line1" class="label">House / flat, street</label>
                <input id="line1" wire:model="line1" class="input">
                @error('line1') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label for="line2" class="label">Area, landmark (optional)</label>
                <input id="line2" wire:model="line2" class="input">
            </div>
            <div>
                <label for="city" class="label">City / town</label>
                <input id="city" wire:model="city" class="input">
                @error('city') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="pincode" class="label">PIN code</label>
                <input id="pincode" wire:model="pincode" inputmode="numeric" maxlength="6" class="input">
                @error('pincode') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="sm:col-span-2">
                <label for="state" class="label">State</label>
                <select id="state" wire:model="state" class="input">
                    @foreach ($states as $s)
                        <option value="{{ $s }}">{{ $s }}</option>
                    @endforeach
                </select>
                @error('state') <p class="error">{{ $message }}</p> @enderror
            </div>
            <div class="flex gap-3 sm:col-span-2">
                <button type="submit" class="btn-dark">Save address</button>
                <button type="button" wire:click="cancel" class="btn-light">Cancel</button>
            </div>
        </form>
    @else
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($addresses as $address)
                <div wire:key="addr-{{ $address->id }}" class="border p-5 text-sm {{ $address->is_default ? 'border-ink' : 'border-sand' }}">
                    <div class="flex items-start justify-between gap-2">
                        <p class="font-medium">{{ $address->name }}</p>
                        @if ($address->is_default)
                            <span class="bg-ink px-2 py-0.5 text-[10px] tracking-wider text-white uppercase">Default</span>
                        @endif
                    </div>
                    <p class="mt-2 text-neutral-600">{{ $address->oneLine() }}</p>
                    <p class="mt-1 text-neutral-600">{{ $address->phone }}</p>
                    <div class="mt-4 flex gap-4 text-xs">
                        <button type="button" wire:click="edit({{ $address->id }})" class="underline underline-offset-4">Edit</button>
                        @unless ($address->is_default)
                            <button type="button" wire:click="makeDefault({{ $address->id }})" class="underline underline-offset-4">Make default</button>
                        @endunless
                        <button type="button" wire:click="delete({{ $address->id }})" wire:confirm="Delete this address?" class="text-sale underline underline-offset-4">Delete</button>
                    </div>
                </div>
            @endforeach

            <button type="button" wire:click="create" class="flex min-h-36 items-center justify-center border border-dashed border-sand text-sm hover:border-ink">
                + Add new address
            </button>
        </div>
    @endif
</x-account-layout>
