<?php

namespace App\Livewire\Waiter\OrderPickUp;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Order;

class Index extends Component
{
    public $user;

    public $orders;
    #[Layout('components.layouts.waiter.app')]
    public function render()
    {
        return view('livewire.waiter.order-pick-up.index',[
            'orders' => $this->orders,
            'user' => $this->user,
        ]);
    }

    public function mount()
    {
        $this->user = auth()->user();
        $this->orders = Order::where('order_type', 'takeaway')
            ->with(['orderItems.item', 'orderItems.variant'])
            ->get();
    }
}
