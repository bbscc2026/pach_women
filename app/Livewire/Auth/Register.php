<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Create account')]
class Register extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function register()
    {
        // At most 5 new accounts per IP per 10 minutes.
        $key = 'register:'.request()->ip();

        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages([
                'email' => 'Too many sign-ups from this network. Try again in '.ceil(RateLimiter::availableIn($key) / 60).' minutes.',
            ]);
        }

        $data = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['required', 'regex:/^(\+91[\s-]?)?[6-9]\d{9}$/'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'phone.regex' => 'Enter a valid 10-digit Indian mobile number.',
            'email.unique' => 'An account with this email already exists. Try logging in.',
        ]);

        RateLimiter::hit($key, 600);
        $user = User::create($data);

        event(new Registered($user));
        Auth::login($user, remember: true);
        session()->regenerate();

        return $this->redirectIntended(route('account.orders'), navigate: true);
    }

    public function render()
    {
        return view('livewire.auth.register');
    }
}
