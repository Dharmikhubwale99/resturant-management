<?php

namespace App\Livewire\Resturant\Report;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Order;
use App\Models\RestaurantPaymentLog;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;

class MoneyIn extends Component
{
    use WithPagination;

    public $dateFilter = '';
    public $fromDate;
    public $toDate;
    public $methodFilter = '';
    public $selectedOrderId = null;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.report.money-in', [
            'orders' => $this->getFilteredOrders(),
        ]);
    }

    public function updatedDateFilter()
    {
        if ($this->dateFilter === 'custom') {
            $this->fromDate = now()->subDays(7)->format('Y-m-d');
            $this->toDate = now()->format('Y-m-d');
        } else {
            $this->fromDate = null;
            $this->toDate = null;
        }
    }

    public function toggleDetails($orderId)
    {
        $this->selectedOrderId = $this->selectedOrderId === $orderId ? null : $orderId;
    }

    public function getFilteredOrders()
    {
        $query = Order::query()
            ->with(['payments', 'paymentLogs'])
            ->whereBetween('created_at', [$this->fromDate, $this->toDate]);

        // Date Filter
        if ($this->dateFilter === 'weekly') {
            $query->whereBetween('created_at', [now()->subWeek(), now()]);
        } elseif ($this->dateFilter === 'monthly') {
            $query->whereMonth('created_at', now()->month);
        } elseif ($this->dateFilter === 'custom' && $this->fromDate && $this->toDate) {
            $query->whereBetween('created_at', [$this->fromDate, $this->toDate]);
        }

        // Method Filter
        if ($this->methodFilter) {
            $query->whereHas('paymentLogs', function ($q) {
                $q->where('method', $this->methodFilter);
            });
        }

        return $query->paginate(15);
    }
}
