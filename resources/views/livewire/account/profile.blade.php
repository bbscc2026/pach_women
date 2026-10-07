<x-account-layout heading="Profile & password">
    <form wire:submit="saveProfile" class="grid max-w-xl gap-5">
        <div>
            <label for="name" class="label">Full name</label>
            <input id="name" wire:model="name" class="input">
            @error('name') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" type="email" wire:model="email" class="input">
            @error('email') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="phone" class="label">Mobile number</label>
            <input id="phone" type="tel" wire:model="phone" class="input">
            @error('phone') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div class="flex items-center gap-4">
            <button type="submit" class="btn-dark">Save</button>
        </div>
    </form>

    <h3 class="mt-14 font-serif text-xl font-semibold">Change password</h3>
    <form wire:submit="changePassword" class="mt-5 grid max-w-xl gap-5">
        <div>
            <label for="current_password" class="label">Current password</label>
            <x-password-input id="current_password" wire:model="current_password" autocomplete="current-password" />
            @error('current_password') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password" class="label">New password</label>
            <x-password-input id="password" wire:model="password" autocomplete="new-password" />
            @error('password') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password_confirmation" class="label">Confirm new password</label>
            <x-password-input id="password_confirmation" wire:model="password_confirmation" autocomplete="new-password" />
        </div>
        <div class="flex items-center gap-4">
            <button type="submit" class="btn-dark">Update password</button>
        </div>
    </form>
</x-account-layout>
