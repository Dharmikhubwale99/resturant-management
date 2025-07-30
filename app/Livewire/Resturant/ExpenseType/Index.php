<?php

namespace App\Livewire\Resturant\ExpenseType;

use Livewire\Component;
use App\Models\ExpenseType;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

class Index extends Component
{
    use WithPagination;
    public $confirmingDelete = false;
    public $expensetypeToDelete = null;
    public $search = '';

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
         $expensetypes = ExpenseType::where('restaurant_id', auth()->user()->restaurants()->first()->id)
        ->when($this->search, function($query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        })->orderByDesc('id')->paginate(10);

        return view('livewire.resturant.expense-type.index',[
            'expensetypes' => $expensetypes
        ]);
    }

    public function mount()
    {
        if (!setting('expensetype')) {
            abort(403, 'You do not have access to this module.');
        }
    }

    public function confirmDelete($id)
    {
        $this->expensetypeToDelete = $id;
        $this->confirmingDelete = true;
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = false;
        $this->expensetypeToDelete = null;
    }

    public function deleteExpenseType()
    {
        $expensetypes = ExpenseType::find($this->expensetypeToDelete);
        if ($expensetypes) {
            $expensetypes->delete();
            session()->flash('success', 'Expense type deleted successfully.');
        } else {
            session()->flash('error', 'Expense type not found.');
        }

        $this->cancelDelete();
    }
}
