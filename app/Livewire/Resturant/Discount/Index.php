<?php

namespace App\Livewire\Resturant\Discount;

use Livewire\Component;
use App\Models\Discount;
use Livewire\Attributes\Layout;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    public $confirmingDelete = false;
    public $discountToDelete = null;
    public $search = '';
    public $filterDiscountType = '';
    public $confirmingBlock = false;
    public $blockId = null;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $discount = Discount::where('restaurant_id', auth()->user()->restaurants()->first()->id)
        ->when($this->search, function($query) {
            $query->where('code', 'like', '%' . $this->search . '%')
            ->orWhere('value', 'like', '%' . $this->search . '%');
        })
        ->when($this->filterDiscountType, function ($q) {
            $q->where('type', $this->filterDiscountType);
        })
        ->orderByDesc('id')->paginate(10);
        return view('livewire.resturant.discount.index', [
            'discounts' => $discount
        ]);
    }

    public function confirmDelete($id)
    {
        $this->discountToDelete = $id;
        $this->confirmingDelete = true;
    }

    public function cancelDelete()
    {
        $this->confirmingDelete = false;
        $this->discountToDelete = null;
    }

    public function deleteDiscount()
    {
        $discount = Discount::find($this->discountToDelete);
        if ($discount) {
            $discount->delete();
            session()->flash('success', 'Discount deleted successfully.');
        } else {
            session()->flash('error', 'Discount not found.');
        }

        $this->cancelDelete();
    }

    public function confirmBlock($id)
    {
        $this->blockId = $id;
        $this->confirmingBlock = true;
    }

    public function cancelBlock()
    {
        $this->blockId = null;
        $this->confirmingBlock = false;
    }

    public function toggleBlock()
    {
        $discount = Discount::findOrFail($this->blockId);
        $discount->is_active = !$discount->is_active;
        $discount->save();

        $status = $discount->is_active ? 'unblocked' : 'blocked';
        session()->flash('message', "Discount {$status} successfully.");

        $this->cancelBlock();
    }

}
