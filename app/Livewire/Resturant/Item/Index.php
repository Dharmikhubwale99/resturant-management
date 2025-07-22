<?php

namespace App\Livewire\Resturant\Item;

use App\Models\Item;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;

class Index extends Component
{
    use WithPagination;
    public $confirmingDelete = false;
    public $itemToDelete = null;
    public $search = '';
    public $filterItemType = '';

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $restaurantId = auth()->user()->restaurants()->first()->id;

        $items = Item::where('restaurant_id', $restaurantId)
            ->where(function ($query) {
                $query->when($this->search, function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                      ->orWhere('code', 'like', '%' . $this->search . '%')
                      ->orWhere('short_name', 'like', '%' . $this->search . '%')
                      ->orWhere('price', 'like', '%' . $this->search . '%')
                      ->orWhereHas('category', function ($q2) {
                          $q2->where('name', 'like', '%' . $this->search . '%');
                      });
                });
            })
            ->when($this->filterItemType, function ($q) {
                $q->where('item_type', $this->filterItemType);
            })
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.resturant.item.index', [
            'items' => $items
        ]);
    }


    public function confirmDelete($id)
    {
        $this->itemToDelete = $id;
        $this->confirmingDelete = true;
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = false;
        $this->itemToDelete = null;
    }

    public function deleteItem()
    {
        $item = Item::find($this->itemToDelete);
        if ($item) {
            $item->delete();
            session()->flash('success', 'Item deleted successfully.');
        } else {
            session()->flash('error', 'Item not found.');
        }

        $this->cancelDelete();
    }
}
