<?php

namespace App\Livewire\Waiter\OrderPickUp;

use Livewire\Component;
use Livewire\Attributes\Layout;

class Create extends Component
{
    #[Layout('components.layouts.waiter.app')]
    public function render()
    {
        return view('livewire.waiter.order-pick-up.create');
    }
}
