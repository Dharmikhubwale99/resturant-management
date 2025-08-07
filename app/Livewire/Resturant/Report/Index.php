<?php

namespace App\Livewire\Resturant\Report;

use Livewire\Component;
use Livewire\Attributes\Layout;

class Index extends Component
{
    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.report.index');
    }

    public function mount()
    {
        if (!setting('report')) {
            abort(403, 'You do not have access to this module.');
        }
    }
}
