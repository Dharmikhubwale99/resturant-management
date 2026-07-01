<?php

namespace App\Livewire\Resturant\Discount;

use Livewire\Component;
use App\Models\Discount;
use Livewire\Attributes\Layout;

class Edit extends Component
{
    public $discount;
    public $code;
    public $type;
    public $value;
    public $minimum_amount;
    public $max_uses;
    public $starts_at;
    public $ends_at;
    public $resturant;
    public $items = [];
    public $selected_items = [];

    public function mount($id)
    {
        if (!setting('item')) {
            abort(403, 'You do not have access to this module.');
        }

        $this->discount = Discount::findOrFail($id);
        $this->resturant = auth()->user()->restaurants()->first();

        // Populate fields
        $this->code = $this->discount->code;
        $this->type = $this->discount->type;
        $this->value = $this->discount->value;
        $this->minimum_amount = $this->discount->minimum_amount;
        $this->max_uses = $this->discount->max_uses;

        // Format for datetime-local input
        $this->starts_at = $this->discount->starts_at ? $this->discount->starts_at->format('Y-m-d\TH:i') : null;
        $this->ends_at = $this->discount->ends_at ? $this->discount->ends_at->format('Y-m-d\TH:i') : null;

        $this->selected_items = $this->discount->items()->pluck('items.id')->toArray();
        // Load all items for the restaurant
         $this->items = \App\Models\Item::with('category')
        ->where('restaurant_id', $this->resturant->id)
        ->get()
        ->mapWithKeys(function ($item) {
            $category = ($item->category && $item->category->name) ? $item->category->name : '';
            return [$item->id => $item->name . ($category ? ' | ' . $category : '')];
        })
        ->toArray();
    }

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.discount.edit');
    }

    public function submit()
    {
        $rules = [
            'code' => 'required|unique:discounts,code,' . $this->discount->id . ',id,restaurant_id,' . $this->resturant->id,
            'type' => 'required',
            'selected_items'=> 'required|array|min:1',
            'selected_items.*' => 'exists:items,id',
            'max_uses' => 'nullable|integer',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
        ];

        if ($this->type === 'percentage') {
            $rules['value'] = 'required|numeric|min:0';
        } elseif ($this->type === 'fixed') {
            $rules['minimum_amount'] = 'required|numeric|min:0';
        }

        $this->validate($rules);

        $this->discount->update([
            'code' => $this->code,
            'type' => $this->type,
            'value' => $this->value,
            'minimum_amount' => $this->minimum_amount,
            'max_uses' => $this->max_uses,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
        ]);

        $this->discount->items()->sync($this->selected_items);

        session()->flash('success', 'Discount updated successfully!');
        return redirect()->route('restaurant.discount.index');
    }
}
