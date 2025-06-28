<?php

namespace App\Livewire\Resturant\Area;

use App\Models\Area;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Edit extends Component
{
    public $name, $area, $description;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.area.edit');
    }

     public function mount($id) 
    {
        $this->area = Area::find($id);
        $this->fill($this->area->only('name','description'));
    }

    public function submit()
    {
        $this->validate([
            'name' => 'required|unique:areas,name,' . $this->area->id . ',id,restaurant_id,' . $this->area->restaurant_id,
            'description' => 'nullable'
        ]);

        $this->area->update([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        return redirect()->route('restaurant.areas.index')->with('success', 'Area updated successfully.');
    }
}
