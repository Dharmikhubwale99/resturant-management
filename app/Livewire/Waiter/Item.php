<?php

namespace App\Livewire\Waiter;

use App\Models\Table;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Item extends Component
{
    public $items;

    public $categories;

    public $selectedCategory = null;

    #[Layout('components.layouts.waiter.app')]
    public function render()
    {
        return view('livewire.waiter.item', [
            'filteredItems' => $this->getFilteredItems()
        ]);
    }

    public function mount($table_id)
    {
        $table = Table::findOrFail($table_id);

        $this->items = $table->restaurant->items()->get();

        $this->categories = $this->items
            ->pluck('category')
            ->unique('id')
            ->values();
    }

    public function getFilteredItems()
    {
        return $this->selectedCategory
            ? $this->items->where('category_id', $this->selectedCategory)
            : $this->items;
    }

    public function selectCategory($categoryId)
    {
        $this->selectedCategory = $categoryId;
    }

    public function clearCategory()
    {
        $this->selectedCategory = null;
    }
}
