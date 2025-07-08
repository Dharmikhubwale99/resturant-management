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

}
