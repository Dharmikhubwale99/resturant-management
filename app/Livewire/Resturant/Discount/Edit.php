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
    public $maximum_discount;
    public $max_uses;
    public $starts_at;
    public $ends_at;
    public $resturant;

    public function mount($id)
    {
        $this->discount = Discount::findOrFail($id);
        $this->resturant = auth()->user()->restaurants()->first();

        // Populate fields
        $this->code = $this->discount->code;
        $this->type = $this->discount->type;
        $this->value = $this->discount->value;
        $this->minimum_amount = $this->discount->minimum_amount;
        $this->maximum_discount = $this->discount->maximum_discount;
        $this->max_uses = $this->discount->max_uses;

        // Format for datetime-local input
        $this->starts_at = $this->discount->starts_at ? $this->discount->starts_at->format('Y-m-d\TH:i') : null;
        $this->ends_at = $this->discount->ends_at ? $this->discount->ends_at->format('Y-m-d\TH:i') : null;
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
            'max_uses' => 'nullable|integer',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
        ];

        if ($this->type === 'percentage') {
            $rules['value'] = 'required|numeric|min:0';
        } elseif ($this->type === 'fixed') {
            $rules['minimum_amount'] = 'required|numeric|min:0';
            $rules['maximum_discount'] = 'required|numeric|min:0';
        }

        $this->validate($rules);

        $this->discount->update([
            'code' => $this->code,
            'type' => $this->type,
            'value' => $this->value,
            'minimum_amount' => $this->minimum_amount,
            'maximum_discount' => $this->maximum_discount,
            'max_uses' => $this->max_uses,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
        ]);

        session()->flash('success', 'Discount updated successfully!');
        return redirect()->route('restaurant.discount.index');
    }
}
