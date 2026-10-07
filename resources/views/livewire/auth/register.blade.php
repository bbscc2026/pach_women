<x-auth-card title="Create account" subtitle="Save your address and see all your orders in one place.">
    <form wire:submit="register" class="space-y-5">
        <div>
            <label for="name" class="label">Full name</label>
            <input id="name" wire:model="name" autocomplete="name" autofocus class="input">
            @error('name') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" type="email" wire:model="email" autocomplete="email" class="input">
            @error('email') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="phone" class="label">Mobile number</label>
            <input id="phone" type="tel" wire:model="phone" autocomplete="tel" placeholder="10-digit mobile" class="input">
            @error('phone') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password" class="label">Password</label>
            <x-password-input id="password" wire:model="password" autocomplete="new-password" />
            @error('password') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <label for="password_confirmation" class="label">Confirm password</label>
            <x-password-input id="password_confirmation" wire:model="password_confirmation" autocomplete="new-password" />
        </div>
        <button type="submit" wire:loading.attr="disabled" class="btn-dark w-full">Create account</button>
    </form>
    <p class="mt-8 text-center text-sm text-neutral-600">
        Already have an account? <a href="{{ route('login') }}" wire:navigate class="font-medium text-ink underline underline-offset-4">Log in</a>
    </p>
</x-auth-card>
