<?php

namespace App\Livewire\Resturant\Category;

use Livewire\Component;
use App\Models\Category;
use Livewire\Attributes\Layout;

class Edit extends Component
{
    public $name, $category;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.category.edit');
    }

    public function mount($id) 
    {
        $this->category = Category::find($id);
        $this->fill($this->category->only('name'));
    }

    public function submit()
    {
        $this->validate([
            'name' => 'required|unique:categories,name,' . $this->category->id . ',id,restaurant_id,' . $this->category->restaurant_id,
        ]);

        $this->category->update([
            'name' => $this->name,
        ]);

        return redirect()->route('restaurant.categories.index')->with('success', 'Category updated successfully.');
    }
}
