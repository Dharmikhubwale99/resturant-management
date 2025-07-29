<?php

namespace App\Livewire\Resturant\Report;

use Livewire\Component;
use Livewire\Attributes\Layout;

class StaffWise extends Component
{
    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.report.staff-wise');
    }
}
