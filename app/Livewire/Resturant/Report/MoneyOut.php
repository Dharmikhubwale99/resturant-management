<?php

namespace App\Livewire\Resturant\Report;

use App\Models\{SalesSummariesm, MoneyOut as MoneyOutModel};
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

        $query = MoneyOutModel::where('restaurant_id', $restaurantId);

        if ($this->dateFilter === 'today') {
            $query->whereDate('date', now());
        } elseif ($this->dateFilter === 'weekly') {
            $query->whereBetween('date', [now()->subWeek(), now()]);
        } elseif ($this->dateFilter === 'monthly') {
            $query->whereMonth('date', now()->month);
        } elseif ($this->dateFilter === 'custom' && $this->fromDate && $this->toDate) {
            $query->whereBetween('date', [$this->fromDate, $this->toDate]);
        }

        $sales = $query->orderBy('date', 'desc')->paginate(10);

        return view('livewire.resturant.report.money-out', compact('sales'));
    }
}
