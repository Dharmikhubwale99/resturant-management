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

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {       
        $item = Item::where('restaurant_id', auth()->user()->restaurants()->first()->id)
        ->when($this->search, function ($query, $search) {
            $query->where('name', 'like', '%' . $search . '%');
        })
        ->orderByDesc('id')
        ->paginate(10);
        dd($this->itemTypeFilter);
        return view('livewire.resturant.item.index', [
            'items' => $item
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
