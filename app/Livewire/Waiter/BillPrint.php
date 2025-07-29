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
        $this->order = $order->load([
            'orderItems',
            'orderItems.item',
            'orderItems.variant',
            'table'
        ]);

    }

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.waiter.bill-print');
    }
}

