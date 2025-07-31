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
    public $fromDate;
    public $toDate;
    public $dateFilter = 'today';

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $this->applyDateFilter(); // Apply date filter and set fromDate/toDate

        $restaurantId = Auth::user()->restaurants()->first()->id;

        $staffList = User::whereHas('orders', function ($query) use ($restaurantId) {
                $query->where('restaurant_id', $restaurantId);
            })
            ->where(function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('mobile', 'like', '%' . $this->search . '%');
            })
            ->withCount(['orders as total_orders' => function ($query) use ($restaurantId) {
                $query->where('restaurant_id', $restaurantId)
                      ->when($this->fromDate && $this->toDate, function ($q) {
                          $q->whereBetween('created_at', [$this->fromDate, $this->toDate]);
                      });
            }])
            ->withSum(['orders as total_sales' => function ($query) use ($restaurantId) {
                $query->where('restaurant_id', $restaurantId)
                      ->when($this->fromDate && $this->toDate, function ($q) {
                          $q->whereBetween('created_at', [$this->fromDate, $this->toDate]);
                      });
            }], 'total_amount')
            ->paginate(10);

        $orders = [];

        if ($this->selectedUserId) {
            $ordersQuery = Order::where('user_id', $this->selectedUserId)
                ->where('restaurant_id', $restaurantId)
                ->with(['items.item', 'table'])
                ->when($this->fromDate && $this->toDate, function ($q) {
                    $q->whereBetween('created_at', [$this->fromDate, $this->toDate]);
                });

            $orders = $ordersQuery->latest()->get();
        }

        return view('livewire.resturant.report.staff-wise', [
            'staffList' => $staffList,
            'orders' => $orders,
        ]);
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function showDetails($userId)
    {
        $this->selectedUserId = $this->selectedUserId === $userId ? null : $userId;
    }

    public function applyDateFilter()
    {
        if ($this->dateFilter === 'today') {
            $this->fromDate = now()->startOfDay()->format('Y-m-d');
            $this->toDate = now()->endOfDay()->format('Y-m-d');
        } elseif ($this->dateFilter === 'weekly') {
            $this->fromDate = now()->subWeek()->startOfDay()->format('Y-m-d');
            $this->toDate = now()->endOfDay()->format('Y-m-d');
        } elseif ($this->dateFilter === 'monthly') {
            $this->fromDate = now()->startOfMonth()->format('Y-m-d');
            $this->toDate = now()->endOfMonth()->format('Y-m-d');
        }
    }
}
