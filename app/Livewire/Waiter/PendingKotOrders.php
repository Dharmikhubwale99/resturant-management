<?php

namespace App\Livewire\Waiter;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Kot;        // ← adjust if your model name / namespace differs

class PendingKotOrders extends Component
{
    public $orders = [];

    #[Layout('components.layouts.waiter.app')]
    public function render()
    {
        return view('livewire.waiter.pending-kot-orders');
    }
    public function mount()
    {
        $this->loadOrders();
    }

    public function loadOrders(): void
    {
        // Pull every pending KOT (newest first)
        $this->orders = Kot::where('status', 'pending')
                           ->latest()
                           ->get();
    }

    /** Optional - refresh list from elsewhere */
    protected $listeners = ['kotUpdated' => 'loadOrders'];

}
