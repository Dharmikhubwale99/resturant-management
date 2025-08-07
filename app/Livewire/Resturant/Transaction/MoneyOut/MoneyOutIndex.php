<?php

namespace App\Livewire\Resturant\Transaction\MoneyOut;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\{MoneyOut, Expense};
use Illuminate\Pagination\LengthAwarePaginator;

class MoneyOutIndex extends Component
{
    public $search = '';
    public $from_date;
    public $to_date;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $moneyOutQuery = MoneyOut::query();
        $expenseQuery = Expense::query();

        if ($this->search) {
            $moneyOutQuery->where(function ($q) {
                $q->where('party_name', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            });

            $expenseQuery->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            });
        }

        if ($this->from_date) {
            $moneyOutQuery->whereDate('date', '>=', $this->from_date);
            $expenseQuery->whereDate('paid_at', '>=', $this->from_date);
        }

        if ($this->to_date) {
            $moneyOutQuery->whereDate('date', '<=', $this->to_date);
            $expenseQuery->whereDate('paid_at', '<=', $this->to_date);
        }

        $moneyOuts = $moneyOutQuery->get()->map(function ($item) {
            return [
                'date' => $item->date,
                'amount' => $item->amount,
                'party_name' => $item->party_name,
                'description' => $item->description,
                'type' => 'money_out',
            ];
        });

        $expenses = $expenseQuery->get()->map(function ($item) {
            return [
                'date' => $item->paid_at,
                'amount' => $item->amount,
                'party_name' => $item->name,
                'description' => $item->description,
                'type' => 'expense',
            ];
        });

        $combined = $moneyOuts->merge($expenses)->sortByDesc('date')->values();

        $perPage = 15;
        $currentPage = request()->get('page', 1);
        $pagedData = $combined->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $paginated = new LengthAwarePaginator(
            $pagedData,
            $combined->count(),
            $perPage,
            $currentPage,
            ['path' => request()->url(), 'query' => request()->query()]
        );

        return view('livewire.resturant.transaction.money-out.money-out-index', [
            'records' => $paginated,
        ]);
    }

    public function mount()
    {
        if (!setting('moneyOut')) {
            abort(403, 'You do not have access to this module.');
        }
    }
}
