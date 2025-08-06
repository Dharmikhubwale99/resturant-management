<?php

namespace App\Livewire\Resturant\Expenses;

use App\Models\{Expense, SalesSummaries};
use Livewire\Component;
use Livewire\Attributes\Layout;

class Create extends Component
{
    public $restaurant;
    public $expense_type_id;
    public $name;
    public $amount;
    public $description;
    public $expenseTypes;
    public $paid_at;
    public $expense_total;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $this->restaurant = auth()->user()->restaurants()->first();
        return view('livewire.resturant.expenses.create');
    }
    public function mount()
    {
        if (!setting('expenses')) {
            abort(403, 'You do not have access to this module.');
        }

        $this->restaurant = auth()->user()->restaurants()->first();

        $this->expenseTypes = $this->restaurant
                                ->expenseTypes()
                                ->where('is_active', 0)
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray();

        $this->expense_total = SalesSummaries::where('restaurant_id', $this->restaurant->id)->first();
    }


    public function submit()
    {
        if (setting('expense-type-module')) {
            $this->validate([
                 'expense_type_id' => 'required',
            ]);
        }

        $this->validate([
            'name' => 'required',
            'amount' => 'required',
            'paid_at' => 'nullable',
            'description' => 'nullable',
        ]);

        Expense::create([
            'restaurant_id' => $this->restaurant->id,
            'expense_type_id' => $this->expense_type_id,
            'name' => $this->name,
            'amount' => $this->amount,
            'paid_at' =>$this->paid_at,
            'description' => $this->description,
        ]);

        if (!$this->expense_total || $this->expense_total->summary_date != now()->format('Y-m-d')) {
            SalesSummaries::create([
                'restaurant_id' => $this->restaurant->id,
                'total_expances' => $this->amount,
                'summary_date' => now(),
            ]);
        } else {
            $this->expense_total->update([
                'total_expances' => $this->expense_total->total_sale + $this->amount,
                'summary_date' => now(),
            ]);
        }


        return redirect()->route('restaurant.expenses.index')->with('success', 'Expense created successfully.');
    }
}
