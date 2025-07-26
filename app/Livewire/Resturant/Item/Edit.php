<?php

namespace App\Livewire\Resturant\Item;

use App\Models\Item;
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Enums\ItemType;
use Livewire\Attributes\Layout;
use Illuminate\Validation\ValidationException;
use App\Models\Variant;
use Illuminate\Support\Str;
use App\Models\Addon;


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
    public $restaurant;
    public $variants = [];
    public $addons = [];

     public function render()
    {
        return view('livewire.resturant.item.edit');
    }
    public function mount($id)
    {
        if (!setting('item')) {
            abort(403, 'You do not have access to this module.');
        }

        $this->item = Item::with('media')->findOrFail($id);

        $this->category_id = $this->item->category_id;
        $this->item_type = $this->item->item_type;
        $this->name = $this->item->name;
        $this->short_name = $this->item->short_name;
        $this->code = $this->item->code;
        $this->description = $this->item->description;
        $this->price = $this->item->price;

        $restaurant = auth()->user()->restaurants()->first();
        $this->restaurant = $restaurant;
        $this->categories = $restaurant->categories()->orderBy('name')->pluck('name', 'id')->toArray();
        $this->itemTypes = collect(ItemType::cases())->mapWithKeys(fn ($c) => [$c->value => $c->label()])->toArray();

        // Load existing variants
        $this->variants = $this->item->variants->map(function($variant) {
            return [
                'id' => $variant->id,
                'name' => $variant->name,
                'price' => $variant->price,
            ];
        })->toArray();

        // Load existing addons
        $this->addons = $this->item->addons->map(function($addon) {
            return [
                'id' => $addon->id,
                'name' => $addon->name,
                'price' => $addon->price,
            ];
        })->toArray();
    }

    public function addVariant()
    {
        $this->variants[] = ['id' => null, 'name' => '', 'price' => ''];
    }

    public function removeVariant($index)
    {
        if (!empty($this->variants[$index]['id'])) {
            Variant::find($this->variants[$index]['id'])->delete();
        }
        unset($this->variants[$index]);
        $this->variants = array_values($this->variants); // reindex
    }

    public function removeImage($mediaId)
    {
        $this->item->deleteMedia($mediaId);
        $this->item->refresh();
    }

    public function getRestaurantFolder(): string
    {
        return Str::slug($this->restaurant->name);
    }


    public function submit()
    {
        if (setting('category_module')) {
            $this->validate([
                'category_id' => 'required',
            ]);
        }

        $this->validate([
            'name' => 'required',
            'item_type' => 'required',
            'short_name' => 'nullable|unique:items,short_name,'. $this->item->id . ',id,restaurant_id,' . $this->item->restaurant_id,
            'code' => 'nullable|unique:items,code,'. $this->item->id . ',id,restaurant_id,' . $this->item->restaurant_id,
            'description' => 'nullable',
            'price' => 'required|numeric',
        ]);

        $isExists = Item::where([
            'restaurant_id' => $this->restaurant->id,
            'category_id'   => $this->category_id,
            'name'          => $this->name,
        ])->whereNot('id', $this->item->id)->exists();

        if ($isExists) {
            throw ValidationException::withMessages([
                'name' => 'An item with the same name already exists in this category.',
            ]);
        }

        $this->item->update([
            'category_id' => $this->category_id,
            'name' => $this->name,
            'item_type' => $this->item_type,
            'short_name' => $this->short_name,
            'code' => $this->code,
            'description' => $this->description,
            'price' => $this->price,
        ]);

        foreach ($this->variants as $variant) {
            if (!empty($variant['name']) && !empty($variant['price'])) {
                if (!empty($variant['id'])) {
                    // Update existing
                    Variant::where('id', $variant['id'])->update([
                        'name' => $variant['name'],
                        'price' => $variant['price'],
                    ]);
                } else {
                    // Create new
                    $this->item->variants()->create([
                        'name' => $variant['name'],
                        'price' => $variant['price'],
                    ]);
                }
            }
        }

        foreach ($this->addons as $addon) {
            if (!empty($addon['name']) && !empty($addon['price'])) {
                if (!empty($addon['id'])) {
                    // Update existing
                    Addon::where('id', $addon['id'])->update([
                        'name' => $addon['name'],
                        'price' => $addon['price'],
                    ]);
                } else {
                    // Create new
                    $this->item->addons()->create([
                        'name' => $addon['name'],
                        'price' => $addon['price'],
                    ]);
                }
            }
        }

        if (is_array($this->images)) {
            foreach ($this->images as $image) {
                $folder = 'images/' . $this->getRestaurantFolder();

                $originalName = $image->getClientOriginalName();
                $fileName = uniqid() . '-' . $originalName;

                $storedPath = $image->storeAs($folder, $fileName, 'public');

                $this->item->addMedia(storage_path("app/public/{$storedPath}"))
                    ->preservingOriginal()
                    ->usingName(pathinfo($fileName, PATHINFO_FILENAME))
                    ->usingFileName($fileName)
                    ->toMediaCollection('images');
            }
        }
        return redirect()->route('restaurant.items.index')->with('success', 'Item updated successfully.');
    }

    public function addAddon()
    {
        $this->addons[] = ['id' => null, 'name' => '', 'price' => ''];
    }

    public function removeAddon($index)
    {
        if (!empty($this->addons[$index]['id'])) {
            Addon::find($this->addons[$index]['id'])->delete();
        }
        unset($this->addons[$index]);
        $this->addons = array_values($this->addons); // reindex
    }
}
