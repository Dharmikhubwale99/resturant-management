<?php

namespace App\Livewire\Resturant\Expenses;

use App\Models\Expense;
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
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray();
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

        return redirect()->route('restaurant.expenses.index')->with('success', 'Expense created successfully.');
    }
}
