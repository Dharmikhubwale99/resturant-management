<?php

namespace App\Livewire\Waiter;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Table;

class Dashboard extends Component
{
    public $tables;

    public $showConfirm = false;

    public $selectedTable = null;

    #[Layout('components.layouts.waiter.app')]
    public function render()
    {
        return view('livewire.waiter.dashboard',[
            'tables' => $this->tables,
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

}
