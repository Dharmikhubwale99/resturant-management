<?php

namespace App\Livewire\Waiter;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\{Kot, KOTItem};

class KotPrint extends Component
{
    public $kot;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.waiter.kot-print')
            ->layout('components.layouts.print');
    }

    public function mount($kot_id)
    {
        $this->kot = Kot::with(['kotItems.item', 'kotItems.variant', 'table'])->findOrFail($kot_id);
    }
}
