<?php

namespace App\Livewire\Resturant\Item;

use App\Models\Item;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ItemImport;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;

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
    public $confirmingBlock = false;
    public $itemId = null;

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

    public function confirmBlock($id)
    {
        $this->itemId = $id;
        $this->confirmingBlock = true;
    }

    public function cancelBlock()
    {
        $this->itemId = null;
        $this->confirmingBlock = false;
    }

    public function toggleBlock()
    {
        $plan = Item::findOrFail($this->itemId);
        $plan->is_active = !$plan->is_active;
        $plan->save();

        $status = $plan->is_active ? 'unblocked' : 'blocked';
        session()->flash('message', "Item {$status} successfully.");

        $this->cancelBlock();
    }

    protected function restaurantId(): int
    {
        return auth()->user()->restaurants()->first()->id;
    }

    #[On('fileSelected')]
    public function fileSelected($itemId = null, $url = null): void
    {
        if (!$itemId || !$url) {
            session()->flash('error', 'Invalid image selection.');
            return;
        }

        $item = Item::where('restaurant_id', auth()->user()->restaurants()->first()->id)
                    ->find($itemId);

        if (!$item) {
            session()->flash('error', 'Item not found.');
            return;
        }

        $item->image_url = $url;
        $item->save();

        session()->flash('success', 'Image updated successfully.');
    }
}
