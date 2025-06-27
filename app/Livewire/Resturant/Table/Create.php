<?php

namespace App\Livewire\Resturant\Table;

use App\Models\Table;
use App\Models\Area;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Str;

class Create extends Component
{
    public $area_id;
    public $name;
    public $capacity;
    public $status = 'available';
    public $qr_enabled = true;
    public $areas = [];

    #[Layout('components.layouts.resturant.app')]
    public function mount()
    {
        $restaurant = auth()->user()->restaurants()->first();
        $this->areas = $restaurant->areas()->pluck('name', 'id')->toArray();
    }

    public function render()
    {
        return view('livewire.resturant.table.create');
    }

    public function submit()
    {
        $this->validate([
            'area_id' => 'required|exists:areas,id',
            'name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,reserved',
            'qr_enabled' => 'boolean',
        ]);

        Table::create([
            'restaurant_id' => auth()->user()->restaurants()->first()->id,
            'area_id' => $this->area_id,
            'name' => $this->name,
            'capacity' => $this->capacity,
            'status' => $this->status,
            'qr_enabled' => $this->qr_enabled,
            'qr_token' => Str::uuid(),
        ]);

        return redirect()->route('restaurant.tables.index')->with('success', 'Table created successfully.');
    }
}
