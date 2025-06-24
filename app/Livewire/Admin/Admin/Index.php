<?php

namespace App\Livewire\Admin\Admin;

use Livewire\Attributes\Layout;
use Livewire\Component;

class Index extends Component
{
    #[Layout('components.layouts.superadmin.app')]
    public function render()
    {
        return view('livewire.admin.admin.index');
    }
}
