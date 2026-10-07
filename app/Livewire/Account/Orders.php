<?php

namespace App\Livewire\Account;

use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('My orders')]
class Orders extends Component
{
    use WithPagination;

    public function render()
    {
        return view('livewire.account.orders', [
            'orders' => auth()->user()->orders()->withCount('items')->latest()->paginate(10),
        ]);
    }
}
