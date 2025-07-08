<?php

namespace App\Livewire\Resturant\Discount;

use Livewire\Component;
use App\Models\{Discount, Item};
use Livewire\Attributes\Layout;

class Create extends Component
{
    public $code;
    public $type;
    public $value;
    public $minimum_amount;
    public $maximum_discount;
    public $max_uses;
    public $starts_at;
    public $ends_at;
    public $resturant;
    public $items = [];
    public $selected_items = [];   

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.discount.create');
    }

    public function mount(): void
    {
        $this->resturant = auth()->user()->restaurants()->first();
        $this->items = Item::where('restaurant_id', $this->resturant->id)
                           ->orderBy('name')
                           ->pluck('name', 'id')
                           ->toArray();
    }

    public function submit()
    {
        $rules = [
            'code' => 'required|unique:discounts,code,NULL,id,restaurant_id,' . $this->resturant->id,
            'type' => 'required',
            'selected_items'=> 'required|array|min:1',     
            'selected_items.*' => 'exists:items,id', 
            'max_uses' => 'nullable|integer',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
        ];

        // Conditional rules
        if ($this->type === 'percentage') {
            $rules['value'] = 'required|numeric|min:0';
        } elseif ($this->type === 'fixed') {
            $rules['minimum_amount'] = 'required|numeric|min:0';
            $rules['maximum_discount'] = 'required|numeric|min:0';
        }

        $validated = $this->validate($rules);

        $discount = Discount::create([
            'restaurant_id'    => $this->resturant->id,
            'code'             => $this->code,
            'type'             => $this->type,
            'value'            => $this->value,
            'minimum_amount'   => $this->minimum_amount,
            'maximum_discount' => $this->maximum_discount,
            'max_uses'         => $this->max_uses,
            'starts_at'        => $this->starts_at,
            'ends_at'          => $this->ends_at,
            'is_active'        => true,
        ]);

        $discount->items()->sync($this->selected_items);   // ⑤ NEW
        
        session()->flash('success', 'Discount created successfully!');
        return redirect()->route('restaurant.discount.index');
    }

    public function generateCode()
    {
        $this->code = strtoupper('HW' . rand(1000, 9999)); 
    }

}
