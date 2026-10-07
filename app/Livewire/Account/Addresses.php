<?php

namespace App\Livewire\Account;

use App\Models\Address;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Addresses')]
class Addresses extends Component
{
    public bool $editing = false;

    #[Locked]
    public ?int $editingId = null;

    public string $name = '';

    public string $phone = '';

    public string $line1 = '';

    public string $line2 = '';

    public string $city = '';

    public string $state = 'Kerala';

    public string $pincode = '';

    public function create(): void
    {
        $this->resetForm();
        $this->name = auth()->user()->name;
        $this->phone = auth()->user()->phone ?? '';
        $this->editing = true;
    }

    public function edit(int $id): void
    {
        $address = $this->find($id);

        $this->resetForm();
        $this->editingId = $address->id;
        $this->fill($address->only('name', 'phone', 'line1', 'city', 'state', 'pincode'));
        $this->line2 = $address->line2 ?? '';
        $this->editing = true;
    }

    public function save(): void
    {
        $data = $this->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^(\+91[\s-]?)?[6-9]\d{9}$/'],
            'line1' => ['required', 'string', 'max:200'],
            'line2' => ['nullable', 'string', 'max:200'],
            'city' => ['required', 'string', 'max:100'],
            'state' => ['required', Rule::in(config('shop.states'))],
            'pincode' => ['required', 'digits:6'],
        ], ['phone.regex' => 'Enter a valid 10-digit Indian mobile number.']);

        $user = auth()->user();

        if ($this->editingId) {
            $this->find($this->editingId)->update($data);
        } else {
            $user->addresses()->create([...$data, 'is_default' => ! $user->addresses()->exists()]);
        }

        $this->resetForm();
        $this->dispatch('notify', message: 'Address saved');
    }

    public function makeDefault(int $id): void
    {
        $address = $this->find($id);

        auth()->user()->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);
    }

    public function delete(int $id): void
    {
        $this->find($id)->delete();
        $this->dispatch('notify', message: 'Address deleted');
    }

    public function cancel(): void
    {
        $this->resetForm();
    }

    private function find(int $id): Address
    {
        return auth()->user()->addresses()->findOrFail($id);
    }

    private function resetForm(): void
    {
        $this->reset('editing', 'editingId', 'name', 'phone', 'line1', 'line2', 'city', 'state', 'pincode');
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.account.addresses', [
            'addresses' => auth()->user()->addresses()->orderByDesc('is_default')->latest()->get(),
            'states' => config('shop.states'),
        ]);
    }
}
