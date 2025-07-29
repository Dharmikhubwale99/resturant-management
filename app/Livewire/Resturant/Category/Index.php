<?php

namespace App\Livewire\Resturant\Category;

use Livewire\Component;
use App\Models\Category;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

class Index extends Component
{
    use WithPagination;
    public $confirmingDelete = false;
    public $categoryToDelete = null;
    public $search = '';

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $category = Category::where('restaurant_id', auth()->user()->restaurants()->first()->id)
        ->when($this->search, function($query) {
            $query->where('name', 'like', '%' . $this->search . '%');
        })->orderByDesc('id')->paginate(10);
        return view('livewire.resturant.category.index', [
            'categories' => $category
        ]);
    }

    public function mount()
    {
        if (!setting('category_module')) {
            abort(403, 'You do not have access to this module.');
        }
    }

    public function confirmDelete($id)
    {
        $this->categoryToDelete = $id;
        $this->confirmingDelete = true;
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = false;
        $this->categoryToDelete = null;
    }

    public function deleteCategory()
    {
        $category = Category::find($this->categoryToDelete);
        if ($category) {
            $category->delete();
            session()->flash('success', 'Category deleted successfully.');
        } else {
            session()->flash('error', 'Category not found.');
        }

        $this->cancelDelete();
    }
}
