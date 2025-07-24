<?php

namespace App\Livewire\Resturant\Table;

use App\Models\Table;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Edit extends Component
{
    public $table;
    public $area_id;
    public $name;
    public $capacity;
    public $status;
    public $qr_enabled;
    public $areas = [];

    #[Layout('components.layouts.resturant.app')]
    public function mount($id)
    {
        $this->table = Table::findOrFail($id);
        $this->area_id = $this->table->area_id;
        $this->name = $this->table->name;
        $this->capacity = $this->table->capacity;
        $this->qr_enabled = $this->table->qr_enabled;

        $restaurant = auth()->user()->restaurants()->first();
        $this->areas = $restaurant->areas()->pluck('name', 'id')->toArray();
    }

    public function render()
    {
        return view('livewire.resturant.table.edit');
    }

    public function submit()
    {
         if (setting('area_module')) {
            $this->validate([
                'area_id' => 'required|exists:areas,id',
            ]);
        }

        $this->validate([
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'qr_enabled' => 'boolean',
        ]);

        $this->table->update([
            'area_id' => $this->area_id,
            'name' => $this->name,
            'capacity' => $this->capacity,
            'status' => 'available',
            'qr_enabled' => $this->qr_enabled,
        ]);

        return redirect()->route('restaurant.tables.index')->with('success', 'Table updated successfully.');
    }
}
