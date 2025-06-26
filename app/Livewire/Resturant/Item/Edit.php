<?php

namespace App\Livewire\Resturant\Item;

use App\Models\Item;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Enums\ItemType;
use Livewire\Attributes\Layout;

class Edit extends Component
{
    use WithFileUploads;

    #[Layout('components.layouts.resturant.app')]
    public $item;
    public $category_id;
    public $item_type;
    public $name;
    public $short_name;
    public $code;
    public $description;
    public $price;
    public $images = [];
    public $categories;
    public $itemTypes;

    public function mount($id)
    {
        $this->item = Item::findOrFail($id);

        $this->category_id = $this->item->category_id;
        $this->item_type = $this->item->item_type;
        $this->name = $this->item->name;
        $this->short_name = $this->item->short_name;
        $this->code = $this->item->code;
        $this->description = $this->item->description;
        $this->price = $this->item->price;

        $restaurant = auth()->user()->restaurants()->first();
        $this->categories = $restaurant->categories()->orderBy('name')->pluck('name', 'id')->toArray();
        $this->itemTypes = collect(ItemType::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->toArray();
    }

    public function submit()
    {
        $this->validate([
            'category_id' => 'required',
            'name' => 'required',
            'item_type' => 'required',
            'short_name' => 'nullable',
            'code' => 'nullable',
            'description' => 'nullable',
            'price' => 'required|numeric',
        ]);

        $this->item->update([
            'category_id' => $this->category_id,
            'name' => $this->name,
            'item_type' => $this->item_type,
            'short_name' => $this->short_name,
            'code' => $this->code,
            'description' => $this->description,
            'price' => $this->price,
        ]);

        // Optional: handle new images
        if (is_array($this->images)) {
            foreach ($this->images as $image) {
                $this->item->addMedia($image)->toMediaCollection('images');
            }
        }

        return redirect()->route('restaurant.items.index')->with('success', 'Item updated successfully.');
    }

    public function render()
    {
        return view('livewire.resturant.item.edit');
    }
}
