<?php

namespace App\Livewire\Resturant\Item;

use App\Models\{Item, Category};
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Enums\ItemType;
use Livewire\WithFileUploads;
use Illuminate\Validation\ValidationException;

class Create extends Component
{
    use WithFileUploads;
    public $category_id;
    public $item_type;
    public $name;
    public $short_name;
    public $code;
    public $description;
    public $price;
    public $restaurant;
    public $categories;
    public $images = [];
    public $itemTypes = [];
    public $variants = []; 

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
            'short_name' => 'nullable|unique:items,short_name,null,id,restaurant_id,' . $this->restaurant->id,
            'code' => 'nullable|unique:items,code,null,id,restaurant_id,' . $this->restaurant->id,
            'description' => 'nullable',
            'price' => 'required|numeric',
        ]);

        $isExists = Item::where([
            'restaurant_id' => $this->restaurant->id,
            'category_id'   => $this->category_id,
            'name'          => $this->name,
        ])->exists();

        if ($isExists) {
            if ($isExists) {
                throw ValidationException::withMessages([
                    'name' => 'An item with the same name already exists in this category.',
                ]);
            }

        }

        $item = Item::create([
            'restaurant_id' => $this->restaurant->id,
            'category_id'   => $this->category_id,
            'name'          => $this->name,
            'item_type'     => $this->item_type,
            'short_name'    => $this->short_name,
            'code'          => $this->code ?? null,
            'description'   => $this->description,
            'price'         => $this->price,
        ]);

        foreach ($this->images as $image) {
            $item->addMedia($image)->toMediaCollection('images');
        }

        foreach ($this->variants as $variant) {
            if (!empty($variant['name']) && !empty($variant['price'])) {
                $item->variants()->create([
                    'name' => $variant['name'],
                    'price' => $variant['price'],
                ]);
            }
        }

        return redirect()->route('restaurant.items.index')->with('success', 'Item created successfully.');
    }

    public function addVariant()
    {
        $this->variants[] = ['name' => '', 'price' => ''];
    }

    public function removeVariant($index)
    {
        unset($this->variants[$index]);
        $this->variants = array_values($this->variants); 
    }
}
