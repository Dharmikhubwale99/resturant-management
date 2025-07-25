<?php

namespace App\Livewire\Waiter;

use Livewire\Component;
use App\Models\Order;
use Livewire\Attributes\Layout;

class BillPrint extends Component
{
    public Order $order;

    public function mount(Order $order)
    {
        // Eager load relationships: items, item, variant
        $this->order = $order->load([
            'orderItems',             // Correct relationship name
            'orderItems.item',
            'orderItems.variant',
            'table'
        ]);

    }

    #[Layout('components.layouts.waiter.app')]
    public function render()
    {
        return view('livewire.waiter.bill-print');
    }
}

