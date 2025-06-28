<?php

namespace App\Livewire\Resturant\User;

use Livewire\Component;
use Livewire\Attributes\Layout;

class Index extends Component
{
    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.user.index');
    }
}
