<?php

namespace App\Livewire\Waiter;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Table;

class Dashboard extends Component
{
    public $showConfirm = false;
    public $selectedTable = null;
    public $tables;

    #[Layout('components.layouts.waiter.app')]
    public function render()
    {
        // Group tables by area name (or 'No Area' if null)
        $tablesByArea = $this->tables->groupBy(function ($table) {
            return $table->area->name ?? 'No Area';
        });

        return view('livewire.waiter.dashboard', [
            'tablesByArea' => $tablesByArea,
        ]);
    }

    public function mount()
    {
        $restaurantId = auth()->user()->restaurant_id;

        $this->tables = Table::with('area')
            ->where('restaurant_id', $restaurantId)
            ->get();

    }

    public function openConfirm($tableId)
    {
        $this->selectedTable = Table::findOrFail($tableId);
        $this->showConfirm = true;
    }

    public function editTable($tableId)
    {
        return redirect()->route('waiter.item', [
            'table_id' => $tableId,
            'mode'     => 'edit'
        ]);
    }

}
