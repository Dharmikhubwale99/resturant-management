<?php

namespace App\Livewire\Resturant\Report;

use App\Models\SalesSummaries;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

class MoneyOut extends Component
{
    use WithPagination;

    public $dateFilter = 'today';
    public $fromDate;
    public $toDate;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $restaurantId = auth()->user()->restaurants()->first()->id;

        $query = SalesSummaries::where('restaurant_id', $restaurantId);

        // 📅 Apply Date Filter
        if ($this->dateFilter === 'today') {
            $query->whereDate('summary_date', now());
        } elseif ($this->dateFilter === 'weekly') {
            $query->whereBetween('summary_date', [now()->subWeek(), now()]);
        } elseif ($this->dateFilter === 'monthly') {
            $query->whereMonth('summary_date', now()->month);
        } elseif ($this->dateFilter === 'custom' && $this->fromDate && $this->toDate) {
            $query->whereBetween('summary_date', [$this->fromDate, $this->toDate]);
        }

        $sales = $query->orderBy('summary_date', 'desc')->paginate(10);

        return view('livewire.resturant.report.money-out', compact('sales'));
    }
}
