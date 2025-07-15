<?php

namespace App\Livewire\Waiter;

use App\Models\Order;
use Livewire\Component;
use Livewire\Attributes\Layout;

class PickupCreate extends Component
{
    public $customer_name, $mobile;
    public $takeawayOrders;
    public $customer_form = false;

    #[Layout('components.layouts.waiter.app')]
    public function render()
    {
        $this->takeawayOrders = Order::where('restaurant_id', auth()->user()->restaurant_id)
        ->where('order_type', 'takeaway')
        ->latest()
        ->get();

        return view('livewire.waiter.pickup-create');
    }

    public function showCustomerForm()
    {
        $this->customer_form = true;
    }

    public function hideCustomerForm()
    {
        $this->customer_form = false;
    }

    public function editTable($id)
    {
        return redirect()->route('waiter.pickup.item', [
            'id'   => $id,
            'mode' => 'edit'
        ]);
    }

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
}
