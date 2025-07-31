<?php

namespace App\Livewire\Resturant\Expenses;

use App\Models\{Expense, SalesSummaries};
use Livewire\Component;
use Livewire\Attributes\Layout;

class Edit extends Component
{
    public $restaurant;
    public $expense_type_id;
    public $name;
    public $amount;
    public $paid_at;
    public $description;
    public $expenseTypes;
    public $expense;
    public $expense_total;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.expenses.edit');
    }

    public function mount($id)
    {
        if (!setting('expenses')) {
            abort(403, 'You do not have access to this module.');
        }

        $this->expense = Expense::findOrFail($id);
        $this->expense_type_id = $this->expense->expense_type_id;
        $this->name = $this->expense->name;
        $this->amount = $this->expense->amount;
        $this->paid_at = $this->expense->paid_at ? \Carbon\Carbon::parse($this->expense->paid_at)->format('Y-m-d') : null;
        $this->description = $this->expense->description;

        $restaurant = auth()->user()->restaurants()->first();

        $this->expenseTypes = $restaurant->expenseTypes()->pluck('name', 'id')->toArray();
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

        $this->expense->update([
            'expense_type_id' => $this->expense_type_id,
            'name' => $this->name,
            'amount' => $this->amount,
            'paid_at' => $this->paid_at,
            'description' => $this->description,
        ]);

        if ($this->expense_total->summary_date != now()->format('Y-m-d')) {
            SalesSummaries::create([
                'restaurant_id' => $this->restaurant->id,
                'total_sale' => $this->amount,
                'summary_date' => now(),
            ]);
        } else {
            $this->expense_total->update([
                'total_sale' => $this->expense_total->total_sale + $this->amount,
                'summary_date' => now(),
            ]);
        }

        return redirect()->route('restaurant.expenses.index')->with('success', 'Expense updated successfully.');
    }
}
