<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\{Plan, Restaurant};
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RestaurantsExport;

class Dashboard extends Component
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
    public $todayRestaurants;
    public $todayFreeTrialCount;
    public $user;
    public $todayincome;
    public $todayDealerSalesCount = 0;
    public $totalDealerSalesCount = 0;


    #[Layout('components.layouts.admin.app')]
    public function render()
    {
        $this->loadStats();
        return view('livewire.admin.dashboard');
    }

    protected function baseRestaurantQuery()
    {
        $q = Restaurant::query()->with(['plan', 'user']);

        if (Auth::check() && Auth::user()->hasRole('dealer')) {
            $q->whereHas('user', function ($uq) {
                $uq->where('referred_by', Auth::id());
            });
        }

        return $q;
    }

    protected function loadStats()
    {
        $this->user = Auth::user();
        $countBase = $this->baseRestaurantQuery()->clone();
        $base = $this->baseRestaurantQuery()->clone();

        $this->todayDealerSalesCount = Restaurant::query()
            ->whereHas('user', fn($uq) => $uq->whereNotNull('referred_by'))
            ->whereHas('plan', fn($q) => $q->where('price', '>', 0))
            ->whereDate('created_at', \Carbon\Carbon::today())
            ->count();

        $this->totalDealerSalesCount = Restaurant::query()
            ->whereHas('user', fn($uq) => $uq->whereNotNull('referred_by'))
            ->whereHas('plan', fn($q) => $q->where('price', '>', 0))
            ->count();

        $freeBase = (clone $countBase)->whereHas('plan', fn($q) => $q->where('price', 0));

        $this->todayFreeTrialCount = (clone $freeBase)
            ->whereDate('created_at', \Carbon\Carbon::today())
            ->count();

        $this->totalRestaurants = (clone $countBase)->count();

        $this->todayRestaurants = (clone $base)
            ->whereDate('created_at', \Carbon\Carbon::today())
            ->count();

        $this->freeTrialCount = (clone $countBase)
            ->whereHas('plan', function ($q) {
                $q->where('price', 0);
            })
            ->count();

        $incomeQuery   = $this->baseRestaurantQuery()->clone();
        $purchaseQuery = $this->baseRestaurantQuery()->clone();

        if ($this->planType === 'free') {
            $incomeQuery->whereHas('plan', fn($q) => $q->where('price', 0));
            $purchaseQuery->whereHas('plan', fn($q) => $q->where('price', 0));
        } elseif ($this->planType === 'paid') {
            $incomeQuery->whereHas('plan', fn($q) => $q->where('price', '>', 0));
            $purchaseQuery->whereHas('plan', fn($q) => $q->where('price', '>', 0));
        }

        switch ($this->filterPeriod) {
            case 'today':
                $incomeQuery->whereDate('created_at', Carbon::today());
                $purchaseQuery->whereDate('created_at', Carbon::today());
                break;

            case 'monthly':
                $incomeQuery->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                $purchaseQuery->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
                break;

            case 'custom':
                if ($this->startDate && $this->endDate) {
                    $incomeQuery->whereBetween('created_at', [
                        Carbon::parse($this->startDate)->startOfDay(),
                        Carbon::parse($this->endDate)->endOfDay(),
                    ]);
                    $purchaseQuery->whereBetween('created_at', [
                        Carbon::parse($this->startDate)->startOfDay(),
                        Carbon::parse($this->endDate)->endOfDay(),
                    ]);
                }
                break;

            case 'all':
                break;
        }

        $this->totalIncome = $incomeQuery->get()->sum(fn ($resto) => $resto->plan->price ?? 0);
        $this->todayincome = $incomeQuery->whereDate('created_at', Carbon::today())->get()->sum(fn ($resto) => $resto->plan->price ?? 0);
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
            'endDate'   => 'required|date|after_or_equal:startDate',
        ]);

        $this->loadStats();
    }

    public function export()
    {
        $filters = [
            'period'    => $this->filterPeriod,
            'startDate' => $this->startDate,
            'endDate'   => $this->endDate,
            'planType'  => $this->planType,
        ];

        if (auth()->check() && auth()->user()->hasRole('dealer')) {
            $filters['dealer_id'] = auth()->id();
        }

        return Excel::download(new RestaurantsExport($filters), 'restaurants-'.now()->format('Y-m-d').'.xlsx');
    }
}
