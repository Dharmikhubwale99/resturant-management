<?php

namespace App\Livewire\Resturant\Item;

use Livewire\Component;
use Livewire\Attributes\Layout;

class Edit extends Component
{
    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.item.edit');
    }
}
