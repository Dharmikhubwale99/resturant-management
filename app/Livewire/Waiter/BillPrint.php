<?php

namespace App\Livewire\Waiter;

use Livewire\Component;
use App\Models\Order;
use Livewire\Attributes\Layout;
use App\Models\Restaurant;

class BillPrint extends Component
{
    public Order $order;
    public ?Restaurant $restaurant = null;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.waiter.bill-print');
    }

    public function mount(Order $order)
    {
        $this->order = $order->load([
            'orderItems',
            'orderItems.item.taxSetting',
            'orderItems.variant',
            'table'
        ]);

        $this->restaurant = Restaurant::find($order->restaurant_id);

        if (empty($this->order->bill_number)) {
            $this->order->bill_number = Order::generateBillNumber($this->order->restaurant_id);
            $this->order->save();
        }
    }

}

