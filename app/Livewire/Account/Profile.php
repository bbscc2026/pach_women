<?php

namespace App\Livewire\Account;

use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Profile')]
class Profile extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        $user = auth()->user();

        $this->name = $user->name;
        $this->email = $user->email;
        $this->phone = $user->phone ?? '';
    }

    public function saveProfile(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', Rule::unique('users')->ignore(auth()->id())],
            'phone' => ['required', 'regex:/^(\+91[\s-]?)?[6-9]\d{9}$/'],
        ], ['phone.regex' => 'Enter a valid 10-digit Indian mobile number.']);

        auth()->user()->update($data);

        $this->dispatch('notify', message: 'Profile saved');
    }

    public function changePassword(): void
    {
        $this->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        auth()->user()->update(['password' => Hash::make($this->password)]);

        $this->reset('current_password', 'password', 'password_confirmation');
        $this->dispatch('notify', message: 'Password updated');
    }

    public function render()
    {
        return view('livewire.account.profile');
    }
}
