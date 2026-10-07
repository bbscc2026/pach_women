<x-auth-card title="Forgot password" subtitle="Enter your email and we'll send you a link to reset your password.">
    @if ($sent)
        <p class="bg-cream px-4 py-4 text-center text-sm">If an account exists for <strong>{{ $email }}</strong>, a reset link is on its way. Check your inbox.</p>
    @else
        <form wire:submit="sendLink" class="space-y-5">
            <div>
                <label for="email" class="label">Email</label>
                <input id="email" type="email" wire:model="email" autocomplete="email" autofocus class="input">
                @error('email') <p class="error">{{ $message }}</p> @enderror
            </div>
            <button type="submit" wire:loading.attr="disabled" class="btn-dark w-full">Send reset link</button>
        </form>
    @endif
    <p class="mt-8 text-center text-sm">
        <a href="{{ route('login') }}" wire:navigate class="underline underline-offset-4">Back to login</a>
    </p>
</x-auth-card>
