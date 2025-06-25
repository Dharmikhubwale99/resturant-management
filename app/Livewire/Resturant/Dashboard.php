<?php

namespace App\Livewire\Resturant;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;

class Dashboard extends Component
{
    public $user;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.dashboard');
    }

    public function mount()
    {
        $this->user = User::find(auth()->id());
    }
}
