<?php

namespace App\Livewire\Resturant\Item;

use App\Models\Item;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ItemImport;
use Livewire\WithFileUploads;

class Index extends Component
{
    use WithFileUploads;
    use WithPagination;
    public $confirmingDelete = false;
    public $itemToDelete = null;
    public $search = '';
    public $filterItemType = '';
    public $importFile;
    public $showImportModal = false;
    public $importErrors = [];

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

    public function mount()
    {
        if (!setting('item')) {
            abort(403, 'You do not have access to this module.');
        }
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

    public function importItems()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls',
        ]);

        $restaurantId = auth()->user()->restaurants()->first()->id;
        $this->importErrors = [];

        try {
            Excel::import(new ItemImport($restaurantId, $this, setting('category_module')), $this->importFile);
            session()->flash('success', 'Items imported successfully!');
            $this->showImportModal = false;
            $this->importFile = null;
        } catch (\Exception $e) {
            $this->importErrors[] = ['row' => 'N/A', 'error' => $e->getMessage()];
        }
    }
}
