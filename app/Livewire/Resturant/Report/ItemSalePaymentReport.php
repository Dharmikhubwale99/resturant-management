<?php

namespace App\Livewire\Resturant\Report;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ItemSalePaymentReport extends Component
{
    use WithPagination;

    public $fromDate, $toDate, $category_id = 'all';
    public $filterType = 'today';
    public $categories = [];

    public function mount()
    {
        if (!setting('report')) {
            abort(403, 'You do not have access to this module.');
        }

        $this->applyDefaultDateFilter();
        $this->categories = DB::table('categories')->pluck('name', 'id')->toArray();
    }

    public function updatedFilterType()
    {
        $this->resetPage();
        $this->applyDefaultDateFilter();
    }

    public function updatedFromDate()
    {
        $this->resetPage();
    }

    public function updatedToDate()
    {
        $this->resetPage();
    }

    public function applyDefaultDateFilter()
    {
        switch ($this->filterType) {
            case 'weekly':
                $this->fromDate = now()->startOfWeek()->format('Y-m-d');
                $this->toDate = now()->endOfWeek()->format('Y-m-d');
                break;
            case 'monthly':
                $this->fromDate = now()->startOfMonth()->format('Y-m-d');
                $this->toDate = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'custom':
                $this->fromDate = $this->fromDate ?? now()->format('Y-m-d');
                $this->toDate = $this->toDate ?? now()->format('Y-m-d');
                break;
            default: // today
                $today = now()->format('Y-m-d');
                $this->fromDate = $today;
                $this->toDate = $today;
                break;
        }
    }

   public function getReportDataProperty()
   {
        $restaurantId = Auth::user()->restaurants()->first()->id;

        $query = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('items', 'order_items.item_id', '=', 'items.id')
            ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
            ->where('order_items.status', 'served')
            ->whereBetween('order_items.created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59'])
            ->where('orders.restaurant_id', $restaurantId);

        if ($this->category_id !== 'all') {
            $query->where('items.category_id', $this->category_id);
        }

        return $query
            ->select(
                'items.name as item_name',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.final_price) as total_amount')
            )
            ->groupBy('items.id', 'items.name', 'categories.name')
            ->orderByDesc(DB::raw('SUM(order_items.quantity)'))
            ->paginate(20);
    }

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.report.item-sale-payment-report', [
            'data' => $this->reportData,
        ]);
    }
}
