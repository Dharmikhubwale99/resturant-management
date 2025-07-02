<?php

namespace App\Livewire\Resturant\Expenses;

use App\Models\Expense;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Edit extends Component
{
    public $restaurant;
    public $expense_type_id;
    public $name;
    public $amount;
    public $description;
    public $expenseTypes;
    public $expense;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.expenses.edit');
    }

    public function mount($id)
    {
        $this->expense = Expense::findOrFail($id);
        $this->expense_type_id = $this->expense->expense_type_id;
        $this->name = $this->expense->name;
        $this->amount = $this->expense->amount;
        $this->description = $this->expense->description;

        $restaurant = auth()->user()->restaurants()->first();

        $this->expenseTypes = $restaurant->expenseTypes()->pluck('name', 'id')->toArray();
    }

    public function submit()
    {
        $this->validate([
            'expense_type_id' => 'required',
            'name' => 'required',
            'amount' => 'required',
            'description' => 'nullable',
        ]);

        $this->expense->update([
            'expense_type_id' => $this->expense_type_id,
            'name' => $this->name,
            'amount' => $this->amount,
            'description' => $this->description,
        ]);

        return redirect()->route('restaurant.expenses.index')->with('success', 'Expense updated successfully.');
    }
}
