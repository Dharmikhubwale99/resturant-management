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
    public $confirmingBlock = false;
    public $typeId = null;


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

    public function confirmBlock($id)
    {
        $this->typeId = $id;
        $this->confirmingBlock = true;
    }

    public function cancelBlock()
    {
        $this->typeId = null;
        $this->confirmingBlock = false;
    }

    public function toggleBlock()
    {
        $type = ExpenseType::findOrFail($this->typeId);
        $type->is_active = !$type->is_active;
        $type->save();

        $status = $type->is_active ? 'unblocked' : 'blocked';
        session()->flash('message', "Expense type {$status} successfully.");

        $this->cancelBlock();
    }
}
