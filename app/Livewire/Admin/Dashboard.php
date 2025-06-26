<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;

class Dashboard extends Component
{
    #[Layout('components.layouts.admin.app')]
    public function render()
    {
        return view('livewire.admin.dashboard');
    }
}
