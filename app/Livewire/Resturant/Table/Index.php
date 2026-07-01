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
    public $confirmingBlock = false;
    public $tableId = null;

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

    public function mount()
    {
        if (!setting('table')) {
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

        if (!$table) {
            session()->flash('error', 'Table not found.');
            return $this->cancelDelete();
        }

        // ✅ Guard: occupied/reserved હોય તો delete ન કરો
        if (in_array(strtolower($table->status), ['occupied', 'reserved'])) {
            session()->flash('error', 'Occupied or Reserved tables cannot be deleted.');
            return $this->cancelDelete();
        }

        $table->delete();
        session()->flash('success', 'Table deleted successfully.');
        $this->cancelDelete();

        // વૈકલ્પિક: પેજ રીફ્રેશ pagination માટે
        $this->resetPage();
    }

    public function confirmBlock($id)
    {
        $this->tableId = $id;
        $this->confirmingBlock = true;
    }

    public function cancelBlock()
    {
        $this->tableId = null;
        $this->confirmingBlock = false;
    }

    public function toggleBlock()
    {
        $table = Table::findOrFail($this->tableId);
        $table->is_active = !$table->is_active;
        $table->save();

        $status = $table->is_active ? 'unblocked' : 'blocked';
        session()->flash('message', "Table {$status} successfully.");

        $this->cancelBlock();
    }
}
