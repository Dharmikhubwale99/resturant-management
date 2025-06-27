<?php

namespace App\Livewire\Resturant\Category;

use Livewire\Component;
use App\Models\Category;
use Livewire\Attributes\Layout;

class Create extends Component
{
    public $name;
    public $resturant;
    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $this->resturant = auth()->user()->restaurants()->first();
       
        return view('livewire.resturant.category.create');
    }

    public function submit()
    {
       $this->validate([
            'name' => 'required|unique:categories,name,NULL,id,restaurant_id,' . $this->resturant->id,
        ]);

        Category::create([
            'restaurant_id' => $this->resturant->id,
            'name' => $this->name,
        ]);

        session()->flash('success', 'Category created successfully!');
        $this->reset(['name']);

        return redirect()->route('restaurant.categories.index'); 
    }
}
