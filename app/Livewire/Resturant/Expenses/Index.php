<?php

namespace App\Livewire\Resturant\Expenses;

use App\Models\Expense;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    public $search = '';
    public $statusFilter = '';
    public $confirmingDelete = false;
    public $expenseToDelete = null;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $restaurant = auth()->user()->restaurants()->first();
        $expenses = Expense::where('restaurant_id', $restaurant->id)
            ->when($this->search, function($q) {
                $q->where(function($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                          ->orWhere('amount', 'like', "%{$this->search}%")
                          ->orWhereHas('expenseType', function($areaQuery) {
                              $areaQuery->where('name', 'like', "%{$this->search}%");
                          });
                });
            })
            ->with('expenseType','user','customer')
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.resturant.expenses.index', [
            'expenses' => $expenses
        ]);
    }

    public function mount()
    {
        if (!setting('expenses')) {
            abort(403, 'You do not have access to this module.');
        }
    }

    public function updatedStatusFilter()
    {
        $this->resetPage();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function confirmDelete($id)
    {
        $this->expenseToDelete = $id;
        $this->confirmingDelete = true;
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = false;
        $this->expenseToDelete = null;
    }

    public function deleteTable()
    {
        $expense = Expense::find($this->expenseToDelete);
        if ($expense) {
            $expense->delete();
            session()->flash('success', 'Expense deleted successfully.');
        } else {
            session()->flash('error', 'Expense not found.');
        }
        $this->cancelDelete();
    }
}
