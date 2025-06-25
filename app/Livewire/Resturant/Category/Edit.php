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
            'name' => 'required',
        ]);

        $this->category->update([
            'name' => $this->name,
        ]);

        return redirect()->route('resturant.categories.index')->with('success', 'Category updated successfully.');
    }
}
