<?php

namespace App\Livewire;

use App\Models\ContentPage;
use Livewire\Attributes\Title;
use Livewire\Component;

/**
 * The "Me" tab: an app-style menu with account, help pages, contact and install.
 * Open to guests too, so policies and contact details are always one tap away on phones.
 */
#[Title('Me')]
class Me extends Component
{
    public function render()
    {
        $user = auth()->user();

        return view('livewire.me', [
            'user' => $user,
            'pages' => ContentPage::active()->get(['slug', 'title']),
            'openOrders' => $user?->orders()->whereIn('status', ['pending', 'confirmed', 'shipped'])->count() ?? 0,
        ]);
    }
}
