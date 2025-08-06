<?php

namespace App\Livewire\Resturant\Report;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

class PaymentReport extends Component
{
    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.report.payment-report');
    }

    public function mount()
    {
        if (!setting('report')) {
            abort(403, 'You do not have access to this module.');
        }
    }
}
