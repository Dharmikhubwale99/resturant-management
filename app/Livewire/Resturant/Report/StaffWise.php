<?php

namespace App\Livewire\Resturant\Report;

use App\Models\User;
use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;

class StaffWise extends Component
{
    use WithPagination;

    public $selectedUserId = null;
    public $search = '';

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $restaurantId = Auth::user()->restaurants()->first()->id;

        // Staff list with search and pagination
        $staffList = User::where('restaurant_id', $restaurantId)
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('mobile', 'like', '%' . $this->search . '%');
            })
            ->withCount(['orders as total_orders' => function ($query) use ($restaurantId) {
                $query->where('restaurant_id', $restaurantId);
            }])
            ->withSum(['orders as total_sales' => function ($query) use ($restaurantId) {
                $query->where('restaurant_id', $restaurantId);
            }], 'total_amount')
            ->paginate(10);

        $orders = [];

        if ($this->selectedUserId) {
            $orders = Order::where('user_id', $this->selectedUserId)
            ->where('restaurant_id', $restaurantId)
            ->with('items.item')
            ->latest()
            ->get();
        }

        return view('livewire.resturant.report.staff-wise', [
            'staffList' => $staffList,
            'orders' => $orders,
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage(); // reset pagination on new search
    }

    public function showDetails($userId)
    {
        $this->selectedUserId = $userId;
    }
}
