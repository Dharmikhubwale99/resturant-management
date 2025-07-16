<?php

namespace App\Livewire\Waiter;

use App\Models\Order;
use Livewire\Component;
use Livewire\Attributes\Layout;

class PickupCreate extends Component
{
    public $customer_name, $mobile;

    #[Layout('components.layouts.waiter.app')]
    public function submit()
    {
        $this->validate([
            'customer_name' => 'required',
            'mobile' => 'required',
        ]);

        $order = Order::create([
            'restaurant_id' => auth()->user()->restaurant_id, 
            'customer_name' => $this->customer_name,
            'mobile' => $this->mobile,
            'order_type' => 'takeaway',
        ]);

        return redirect()->route('waiter.pickup.item', $order->id);
    }

    public function render()
    {
        return view('livewire.waiter.pickup-create');
    }
}
