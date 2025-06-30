<?php

namespace App\Livewire\Resturant\User;

use App\Models\User;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Edit extends Component
{
    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.user.edit');
    }
}
