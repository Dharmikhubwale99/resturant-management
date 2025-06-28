<?php

namespace App\Livewire\Resturant\Table;

use App\Models\Table;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

class Index extends Component
{
    use WithPagination;

    public $search = '';
    public $statusFilter = '';
    public $confirmingDelete = false;
    public $tableToDelete = null;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $restaurant = auth()->user()->restaurants()->first();
        $tables = Table::where('restaurant_id', $restaurant->id)
            ->when($this->search, function($q) {
                $q->where(function($query) {
                    $query->where('name', 'like', "%{$this->search}%")
                          ->orWhere('capacity', 'like', "%{$this->search}%")
                          ->orWhere('status', 'like', "%{$this->search}%")
                          ->orWhereHas('area', function($areaQuery) {
                              $areaQuery->where('name', 'like', "%{$this->search}%");
                          });
                });
            })
            ->with('area')
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.resturant.table.index', [
            'tables' => $tables,
        ]);
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
        $this->tableToDelete = $id;
        $this->confirmingDelete = true;
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = false;
        $this->tableToDelete = null;
    }

    public function deleteTable()
    {
        $table = Table::find($this->tableToDelete);
        if ($table) {
            $table->delete();
            session()->flash('success', 'Table deleted successfully.');
        } else {
            session()->flash('error', 'Table not found.');
        }
        $this->cancelDelete();
    }
}
