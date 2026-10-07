<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Forgot password')]
class ForgotPassword extends Component
{
    public string $email = '';

    public bool $sent = false;

    public function sendLink(): void
    {
        $this->validate(['email' => ['required', 'email']]);

        // Limit reset emails per IP (the password broker also limits per address).
        $key = 'forgot-password:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            $this->addError('email', 'Too many requests. Please wait a few minutes and try again.');

            return;
        }

        RateLimiter::hit($key, 600);

        // Same message whether or not the account exists, so emails can't be probed.
        Password::sendResetLink(['email' => $this->email]);

        $this->sent = true;
    }

    public function render()
    {
        return view('livewire.auth.forgot-password');
    }
}
