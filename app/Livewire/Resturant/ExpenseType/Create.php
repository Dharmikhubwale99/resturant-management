<?php

namespace App\Livewire\Resturant\ExpenseType;

use App\Models\ExpenseType;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Create extends Component
{
    public $name;
    public $resturant;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $this->resturant = auth()->user()->restaurants()->first();

        return view('livewire.resturant.expense-type.create');
    }

    public function submit()
    {
        $this->validate([
            'name' => 'required|unique:expense_types,name,NULL,id,restaurant_id,' . $this->resturant->id,
        ]);

        ExpenseType::create([
            'restaurant_id' => $this->resturant->id,
            'name' => $this->name,
        ]);

        session()->flash('success', 'Expense type created successfully!');
        $this->reset(['name']);

        return redirect()->route('restaurant.expense-types.index');
    }
}
