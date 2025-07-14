<?php

namespace App\Livewire\Waiter;

use Livewire\Component;
use Livewire\Attributes\Layout;

class PickupItem extends Component
{
    #[Layout('components.layouts.waiter.app')]
    public function render()
    {
        return view('livewire.waiter.pickup-item');
    }
}
