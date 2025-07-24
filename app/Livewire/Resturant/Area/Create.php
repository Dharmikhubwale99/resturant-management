<?php

namespace App\Livewire\Resturant\Area;

use App\Models\Area;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Create extends Component
{
    public $name;
    public $resturant;
    public $description;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $this->resturant = auth()->user()->restaurants()->first();
        return view('livewire.resturant.area.create');
    }

    public function submit()
    {
        $this->validate([
            'name' => 'required|unique:areas,name,NULL,id,restaurant_id,' . $this->resturant->id,
            'description' => 'nullable'
        ]);

        Area::create([
            'restaurant_id' => $this->resturant->id,
            'name' => $this->name,
            'description' => $this->description,
        ]);

        session()->flash('success', 'Area created successfully!');
        $this->reset(['name','description']);

        return redirect()->route('restaurant.areas.index');
    }
}
