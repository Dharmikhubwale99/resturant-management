<?php

namespace App\Livewire\Resturant\Expenses;

use App\Models\Expense;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Show extends Component
{
    public $expense;
    #[Layout('components.layouts.resturant.app')]

    public function render()
    {
        return view('livewire.resturant.expenses.show');
    }

    public function mount($id)
    {
        $this->expense = Expense::with('expenseType')->findOrFail($id);
    }

}
