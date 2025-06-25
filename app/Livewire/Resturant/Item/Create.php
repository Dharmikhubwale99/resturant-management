<?php

namespace App\Livewire\Resturant\Item;

use App\Models\{Item, Category};
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Enums\ItemType;

class Create extends Component
{
    public $category_id;
    public $item_type;
    public $name;
    public $short_name;
    public $code;
    public $description;
    public $price;     
    public $restaurant;      
    public $categories;  

    #[Layout('components.layouts.resturant.app')]

    public function mount(): void
    {
        $this->restaurant = auth()->user()->restaurants()->first();

        // associative array: [id => name]
        $this->categories = $this->restaurant
                                ->categories()
                                ->orderBy('name')
                                ->pluck('name', 'id')  
                                ->toArray();

        // enum → array: ['non_veg' => 'Non-Veg', …]
        $this->itemTypes  = collect(ItemType::cases())
                            ->mapWithKeys(fn ($c) => [$c->value => $c->label()])
                            ->toArray();
    }

    public function render()
    {
        $this->restaurant = auth()->user()->restaurants()->first();
        return view('livewire.resturant.item.create', [
            'itemTypes' => ItemType::cases(),
        ]);
    }

    public function submit()
    {
        $this->validate([
            'category_id' => 'required',
            'name' => 'required',
            'item_type' => 'required',
            'short_name' => 'nullable',
            // 'code' => 'nullable',
            'description' => 'nullable',
            'price' => 'required|numeric',
        ]);

        Item::create([
            'restaurant_id' => $this->restaurant->id,
            'category_id'   => $this->category_id,
            'name'          => $this->name,
            'item_type'     => $this->item_type,
            'short_name'    => $this->short_name,
            // 'code'          => $this->code ?? null,
            'description'   => $this->description,
            'price'         => $this->price,
        ]);

        return redirect()->route('resturant.items.index')->with('success', 'Item created successfully.');
    }
}
