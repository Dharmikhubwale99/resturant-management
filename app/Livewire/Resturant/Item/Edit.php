<?php

namespace App\Livewire\Resturant\Item;

use App\Models\{Item, TaxSetting, Variant, Addon};
use Livewire\Component;
use Livewire\WithFileUploads;
use App\Enums\ItemType;
use Livewire\Attributes\Layout;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

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
    public $categories;
    public $itemTypes;
    public $restaurant;
    public $variants = [];
    public $addons = [];
    public $tax_id;
    public $is_tax_inclusive;
    public $taxOptions = [];
    public $picked_image_url = null;

    private function draftKey(): string
    {
        // unique per item
        return 'item_edit_draft_' . ($this->item->id ?? 'new');
    }

    public function render()
    {
        return view('livewire.resturant.item.edit');
    }

    public function mount($id)
    {
        if (!setting('item')) abort(403, 'You do not have access to this module.');

        $this->item = Item::with(['media', 'variants', 'addons'])->findOrFail($id);

        // INITIAL from DB
        $this->category_id       = $this->item->category_id;
        $this->item_type         = $this->item->item_type;
        $this->name              = $this->item->name;
        $this->short_name        = $this->item->short_name;
        $this->code              = $this->item->code;
        $this->description       = $this->item->description;
        $this->price             = $this->item->price;
        $this->tax_id            = $this->item->tax_id;
        $this->is_tax_inclusive  = $this->item->is_tax_inclusive;
        $this->picked_image_url  = $this->item->image_url;

        $restaurant = auth()->user()->restaurants()->first();
        $this->restaurant = $restaurant;

        $this->categories = $restaurant->categories()
            ->where('is_active', 0)->orderBy('name')->pluck('name', 'id')->toArray();

        $this->itemTypes = collect(ItemType::cases())
            ->mapWithKeys(fn ($c) => [$c->value => $c->label()])->toArray();

        // existing variants/addons
        $this->variants = $this->item->variants->map(fn ($v) => [
            'id' => $v->id, 'name' => $v->name, 'price' => $v->price,
        ])->toArray();

        $this->addons = $this->item->addons->map(fn ($a) => [
            'id' => $a->id, 'name' => $a->name, 'price' => $a->price,
        ])->toArray();

        $this->taxOptions = TaxSetting::where('is_active', 0)
            ->orderBy('rate')->pluck('name', 'id')->toArray();

        // 🔁 Restore draft if we came back from File Manager
        if ($draft = session($this->draftKey())) {
            foreach ($draft as $k => $v) {
                if (property_exists($this, $k)) $this->$k = $v;
            }
        }

        // 📎 If redirected back with the picked image, set it and save draft
        if (request()->filled('picked')) {
            $this->picked_image_url = request('picked');
            $this->saveDraft();
        }
    }

    /* ------------ DRAFT: keep form values while visiting File Manager ------------ */
    private function snapshot(): array
    {
        return [
            'category_id'       => $this->category_id,
            'item_type'         => $this->item_type,
            'name'              => $this->name,
            'short_name'        => $this->short_name,
            'code'              => $this->code,
            'description'       => $this->description,
            'price'             => $this->price,
            'tax_id'            => $this->tax_id,
            'is_tax_inclusive'  => $this->is_tax_inclusive,
            'variants'          => $this->variants,
            'addons'            => $this->addons,
            'picked_image_url'  => $this->picked_image_url,
        ];
    }

    private function saveDraft(): void
    {
        session()->put($this->draftKey(), $this->snapshot());
    }

    public function updated($name, $value)
    {
        // કોઈ પણ field બદલાતાં draft save — values ન ખોવાય
        $this->saveDraft();
    }

    /* ------------ Image picker helpers ------------ */
    public function openPicker()
    {
        // 1) save current form state
        $this->saveDraft();

        // 2) server-side redirect to File Manager in picker mode
        return redirect()->route('fm.view', [
            'picker' => 1,
            'return' => route('restaurant.items.edit', $this->item->id),
        ]);
    }

    public function clearPickedImage(): void
    {
        $this->picked_image_url = null;
        $this->saveDraft();
    }

    /* ------------ Variants/Addons ------------ */
    public function addVariant()
    {
        $this->variants[] = ['id' => null, 'name' => '', 'price' => ''];
    }

    public function removeVariant($index)
    {
        if (!empty($this->variants[$index]['id'])) {
            Variant::find($this->variants[$index]['id'])?->delete();
        }
        unset($this->variants[$index]);
        $this->variants = array_values($this->variants);
        $this->saveDraft();
    }

    public function addAddon()
    {
        $this->addons[] = ['id' => null, 'name' => '', 'price' => ''];
    }

    public function removeAddon($index)
    {
        if (!empty($this->addons[$index]['id'])) {
            Addon::find($this->addons[$index]['id'])?->delete();
        }
        unset($this->addons[$index]);
        $this->addons = array_values($this->addons);
        $this->saveDraft();
    }

    /* ------------ Submit ------------ */
    public function submit()
    {
        if (setting('category_module')) {
            $this->validate(['category_id' => 'required']);
        }

        $this->validate([
            'name' => 'required',
            'item_type' => 'required',
            'short_name' => 'nullable|unique:items,short_name,' . $this->item->id . ',id,restaurant_id,' . $this->item->restaurant_id,
            'code' => 'nullable|unique:items,code,' . $this->item->id . ',id,restaurant_id,' . $this->item->restaurant_id,
            'description' => 'nullable',
            'price' => 'required|numeric',
            'tax_id' => 'nullable|exists:tax_settings,id',
            'is_tax_inclusive' => 'nullable|boolean',
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
            'category_id'       => $this->category_id,
            'name'              => $this->name,
            'item_type'         => $this->item_type,
            'short_name'        => $this->short_name,
            'code'              => $this->code,
            'description'       => $this->description,
            'price'             => $this->price,
            'tax_id'            => $this->tax_id,
            'is_tax_inclusive'  => $this->is_tax_inclusive,
            'image_url'         => $this->picked_image_url,
        ]);

        foreach ($this->variants as $variant) {
            if ($variant['name'] !== '' && $variant['price'] !== '') {
                if (!empty($variant['id'])) {
                    Variant::where('id', $variant['id'])->update([
                        'name' => $variant['name'],
                        'price' => $variant['price'],
                    ]);
                } else {
                    $this->item->variants()->create([
                        'name' => $variant['name'],
                        'price' => $variant['price'],
                    ]);
                }
            }
        }

        foreach ($this->addons as $addon) {
            if ($addon['name'] !== '' && $addon['price'] !== '') {
                if (!empty($addon['id'])) {
                    Addon::where('id', $addon['id'])->update([
                        'name' => $addon['name'],
                        'price' => $addon['price'],
                    ]);
                } else {
                    $this->item->addons()->create([
                        'name' => $addon['name'],
                        'price' => $addon['price'],
                    ]);
                }
            }
        }

        // ✅ Clear draft
        session()->forget($this->draftKey());

        return redirect()->route('restaurant.items.index')
            ->with('success', 'Item updated successfully.');
    }

    public function getRestaurantFolder(): string
    {
        return Str::slug($this->restaurant->name);
    }
}
