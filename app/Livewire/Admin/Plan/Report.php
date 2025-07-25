<?php

namespace App\Livewire\Admin\Plan;

use Livewire\Component;
use App\Models\{Plan, Restaurant};
use Illuminate\Support\Carbon;
use Livewire\Attributes\Layout;
use App\Exports\RestaurantsExport;
use Maatwebsite\Excel\Facades\Excel;

class Report extends Component
{
    public $totalRestaurants;
    public $freeTrialCount;
    public $totalIncome;
    public $purchases;
    public $filterPeriod = 'today'; 
    public $showCustomRange = false;
    public $startDate;
    public $endDate;
    public $planType = 'all';

    #[Layout('components.layouts.admin.app')]
    public function render()
    {
        $this->loadStats();
        return view('livewire.admin.plan.report');
    }

     protected function loadStats()
    {
        $this->totalRestaurants = Restaurant::count();
        $this->freeTrialCount = Restaurant::whereHas('plan', function ($q) {
            $q->where('price', 0);
        })->count();

        // Base queries
        $incomeQuery = Restaurant::with('plan');
        $purchaseQuery = Restaurant::with('plan');

        // Apply plan type filter
        if ($this->planType === 'free') {
            $incomeQuery->whereHas('plan', function ($q) {
                $q->where('price', 0);
            });
            $purchaseQuery->whereHas('plan', function ($q) {
                $q->where('price', 0);
            });
        } elseif ($this->planType === 'paid') {
            $incomeQuery->whereHas('plan', function ($q) {
                $q->where('price', '>', 0);
            });
            $purchaseQuery->whereHas('plan', function ($q) {
                $q->where('price', '>', 0);
            });
        }

        // Apply date filter
        switch ($this->filterPeriod) {
            case 'today':
                $incomeQuery->whereDate('created_at', Carbon::today());
                $purchaseQuery->whereDate('created_at', Carbon::today());
                break;
            case 'monthly':
                $incomeQuery->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                $purchaseQuery->whereBetween('created_at', [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()]);
                break;
            case 'custom':
                if ($this->startDate && $this->endDate) {
                    $incomeQuery->whereBetween('created_at', [
                        Carbon::parse($this->startDate)->startOfDay(),
                        Carbon::parse($this->endDate)->endOfDay()
                    ]);
                    $purchaseQuery->whereBetween('created_at', [
                        Carbon::parse($this->startDate)->startOfDay(),
                        Carbon::parse($this->endDate)->endOfDay()
                    ]);
                }
                break;
            case 'all':
                // No date filter needed
                break;
        }

        $this->totalIncome = $incomeQuery->get()->sum(function ($resto) {
            return $resto->plan->price ?? 0;
        });

        $this->purchases = $purchaseQuery->get();
    }

    public function updatedFilterPeriod()
    {
        $this->showCustomRange = $this->filterPeriod === 'custom';
        $this->loadStats();
    }

    public function updatedPlanType()
    {
        $this->loadStats();
    }

    public function applyCustomRange()
    {
        $this->validate([
            'startDate' => 'required|date',
            'endDate' => 'required|date|after_or_equal:startDate',
        ]);
        
        $this->loadStats();
    }

    public function export()
    {
        $filters = [
            'period' => $this->filterPeriod,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'planType' => $this->planType
        ];

        return Excel::download(new RestaurantsExport($filters), 'restaurants-'.now()->format('Y-m-d').'.xlsx');
    }
}
