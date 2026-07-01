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
    public $confirmingBlock = false;
    public $categoryId = null;

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
        $category = Category::find($id);

        if (!$category) {
            session()->flash('error', 'Category not found.');
            return;
        }

        if ($category->items()->exists()) {
            session()->flash('error', 'This category cannot be deleted because items exist under it.');
            return;
        }

        $this->categoryToDelete = $category;
        $this->confirmingDelete = true;
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = false;
        $this->categoryToDelete = null;
    }

    public function deleteCategory()
    {
        if ($this->categoryToDelete) {
            $this->categoryToDelete->delete();
            session()->flash('success', 'Category deleted successfully.');
        } else {
            session()->flash('error', 'Category not found.');
        }

        $this->cancelDelete();
    }

    public function confirmBlock($id)
    {
        $this->categoryId = $id;
        $this->confirmingBlock = true;
    }

    public function cancelBlock()
    {
        $this->categoryId = null;
        $this->confirmingBlock = false;
    }

    public function toggleBlock()
    {
        $category = Category::findOrFail($this->categoryId);
        $category->is_active = !$category->is_active;
        $category->save();

        $status = $category->is_active ? 'unblocked' : 'blocked';
        session()->flash('message', "Category {$status} successfully.");

        $this->cancelBlock();
    }
}
