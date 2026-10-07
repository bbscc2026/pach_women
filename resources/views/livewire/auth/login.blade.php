<x-auth-card title="Welcome back" subtitle="Log in to check out faster and track your orders.">
    <form wire:submit="login" class="space-y-5">
        <div>
            <label for="email" class="label">Email</label>
            <input id="email" type="email" wire:model="email" autocomplete="email" autofocus class="input">
            @error('email') <p class="error">{{ $message }}</p> @enderror
        </div>
        <div>
            <div class="flex items-center justify-between">
                <label for="password" class="label">Password</label>
                <a href="{{ route('password.request') }}" wire:navigate class="mb-1.5 text-xs underline underline-offset-4">Forgot password?</a>
            </div>
            <x-password-input id="password" wire:model="password" autocomplete="current-password" />
            @error('password') <p class="error">{{ $message }}</p> @enderror
        </div>
        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" wire:model="remember" class="size-4 accent-ink">
            Keep me logged in
        </label>
        <button type="submit" wire:loading.attr="disabled" class="btn-dark w-full">Log in</button>
    </form>
    <p class="mt-8 text-center text-sm text-neutral-600">
        New to PACH? <a href="{{ route('register') }}" wire:navigate class="font-medium text-ink underline underline-offset-4">Create an account</a>
    </p>
</x-auth-card>
