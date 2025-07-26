<?php

namespace App\Livewire\Waiter;

use App\Models\{Order, Restaurant};
use Livewire\Component;
use Livewire\Attributes\Layout;

class PickupCreate extends Component
{
    public $customer_name, $mobile;
    public $takeawayOrders;
    public $customer_form = false;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        if (auth()->user()->restaurant_id) {
            $restaurantId = auth()->user()->restaurant_id;
        } else {
            $restaurantId = Restaurant::where('user_id', auth()->id())->value('id');
        }

        $this->takeawayOrders = Order::where('restaurant_id', $restaurantId)
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
        return redirect()->route('restaurant.pickup.item', [
            'id'   => $id,
            'mode' => 'edit'
        ]);
    }

    public function submit()
    {
        $this->validate([
            'customer_name' => 'required',
        ]);

        if (auth()->user()->restaurant_id) {
            $restaurantId = auth()->user()->restaurant_id;
        } else {
            $restaurantId = Restaurant::where('user_id', auth()->id())->value('id');
        }

        $order = Order::create([
            'restaurant_id' => $restaurantId,
            'customer_name' => $this->customer_name,
            'mobile' => $this->mobile,
            'order_type' => 'takeaway',
        ]);

        return redirect()->route('restaurant.pickup.item', $order->id);
    }
}
