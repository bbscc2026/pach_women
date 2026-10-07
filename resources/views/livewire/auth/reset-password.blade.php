<x-auth-card title="Reset password">
    <form wire:submit="resetPassword" class="space-y-5">
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" type="email" wire:model="email" autocomplete="email" class="input">
            @error('email') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password" class="label">New password</label>
            <x-password-input id="password" wire:model="password" autocomplete="new-password" autofocus />
            @error('password') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password_confirmation" class="label">Confirm new password</label>
            <x-password-input id="password_confirmation" wire:model="password_confirmation" autocomplete="new-password" />
        </div>
        <button type="submit" wire:loading.attr="disabled" class="btn-dark w-full">Reset password</button>
    </form>
</x-auth-card>
