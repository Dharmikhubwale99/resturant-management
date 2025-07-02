<?php

namespace App\Livewire\Resturant\ExpenseType;

use App\Models\ExpenseType;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Edit extends Component
{
     public $name, $expense_type;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.expense-type.edit');
    }

    public function mount($id) 
    {
        $this->expense_type = ExpenseType::find($id);
        $this->fill($this->expense_type->only('name'));
    }

    public function submit()
    {
        $this->validate([
            'name' => 'required|unique:expense_types,name,' . $this->expense_type->id . ',id,restaurant_id,' . $this->expense_type->restaurant_id,
        ]);

        $this->expense_type->update([
            'name' => $this->name,
        ]);

        return redirect()->route('restaurant.expense-types.index')->with('success', 'Expense type updated successfully.');
    }
}
