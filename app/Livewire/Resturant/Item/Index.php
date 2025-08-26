<?php

namespace App\Livewire\Resturant\Item;

use App\Models\Item;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ItemImport;
use Livewire\WithFileUploads;

#[Layout('components.layouts.resturant.app')]
class Index extends Component
{
    use WithFileUploads, WithPagination;

    public $confirmingDelete = false;
    public $itemToDelete = null;
    public $search = '';
    public $filterItemType = '';
    public $importFile;
    public $showImportModal = false;
    public $importErrors = [];
    public $confirmingBlock = false;
    public $itemId = null;

    public function mount(): void
    {
        if (!setting('item')) {
            abort(403, 'You do not have access to this module.');
        }

        $pickedUrl = request()->query('picked');
        $pickedItemId = (int) request()->query('itemId');

        if ($pickedUrl && $pickedItemId) {
            $this->updateItemImage($pickedItemId, $pickedUrl);

            // ❌ OLD: redirect()->route('restaurant.items.index')->send();
            // ✅ NEW (Livewire 3 safe):
            $this->redirectRoute('restaurant.items.index'); // cleans URL too
            return; // stop executing mount
        }
    }

    private function updateItemImage(int $itemId, string $url): void
    {
        $restaurantId = $this->restaurantId();

        $item = Item::where('restaurant_id', $restaurantId)->find($itemId);
        if (!$item) {
            session()->flash('error', 'Item not found or not in your restaurant.');
            return;
        }

        $item->image_url = $url;
        $item->save();

        session()->flash('success', 'Item image updated.');
    }

    public function render()
    {
        $restaurantId = $this->restaurantId();

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
            'items' => $items,
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

    public function importItems()
    {
        $this->validate([
            'importFile' => 'required|file|mimes:xlsx,xls',
        ]);

        $restaurantId = $this->restaurantId();
        $this->importErrors = [];

        try {
            Excel::import(new ItemImport($restaurantId, $this, setting('category_module')), $this->importFile);
            session()->flash('success', 'Items imported successfully!');
            $this->showImportModal = false;
            $this->importFile = null;
        } catch (\Throwable $e) {
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
        $item = Item::findOrFail($this->itemId);
        $item->is_active = !$item->is_active;
        $item->save();

        $status = $item->is_active ? 'unblocked' : 'blocked';
        session()->flash('message', "Item {$status} successfully.");

        $this->cancelBlock();
    }

    protected function restaurantId(): int
    {
        return auth()->user()->restaurants()->first()->id;
    }

    public function openPicker($itemId)
    {
        // 1) Open File Manager in picker mode
        // 2) Return to index with itemId in query, FM will append ?picked=<url>
        return redirect()->route('fm.view', [
            'picker' => 1,
            'return' => route('restaurant.items.index', ['itemId' => $itemId]),
        ]);
    }
}
