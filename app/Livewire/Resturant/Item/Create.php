<?php

namespace App\Livewire\Resturant\Item;

use App\Models\{Item, Addon, TaxSetting};
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Enums\ItemType;
use Livewire\WithFileUploads;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class Create extends Component
{
    use WithFileUploads;

    public $category_id, $item_type, $name, $short_name, $code, $description, $price;
    public $restaurant, $categories, $images = [], $itemTypes = [], $variants = [], $addons = [];
    public $tax_id = null, $is_tax_inclusive = null, $taxOptions = [], $calculated_price = 0;
    public $picked_images = [], $picked_image_url = null;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $this->restaurant = auth()->user()->restaurants()->first();
        return view('livewire.resturant.item.create', ['itemTypes' => ItemType::cases()]);
    }

    public function mount(): void
    {
        if (!setting('item')) abort(403, 'You do not have access to this module.');

        $this->restaurant = auth()->user()->restaurants()->first();

        $this->categories = $this->restaurant->categories()
            ->where('is_active', 0)->orderBy('name')->pluck('name', 'id')->toArray();

        $this->itemTypes = collect(ItemType::cases())
            ->mapWithKeys(fn ($c) => [$c->value => $c->label()])->toArray();

        $this->taxOptions = TaxSetting::where('is_active', 0)->orderBy('rate')
            ->pluck('name', 'id')->toArray();

        // 🔁 Restore draft if exists
        if ($draft = session('item_form_draft')) {
            foreach ($draft as $k => $v) if (property_exists($this, $k)) $this->$k = $v;
        }

        // 📎 Returned from File Manager with ?picked=
        if (request()->filled('picked')) {
            $this->picked_image_url = request('picked');
            $this->saveDraft(); // keep latest in session
        }
    }

    /* ----------------- PRICE HELPERS (unchanged) ----------------- */
    public function updated($propertyName)
    {
        if (in_array($propertyName, ['price', 'tax_id', 'is_tax_inclusive'])) {
            $this->calculateFinalPrice();
            $this->saveDraft();
        }
    }

    public function calculateFinalPrice()
    {
        $tax = TaxSetting::find($this->tax_id);
        $rate = $tax?->rate ?? 0;

        if ($this->price !== null && $this->price !== '' && is_numeric($this->price)) {
            $this->calculated_price = $this->is_tax_inclusive
                ? round($this->price / (1 + ($rate / 100)), 2)
                : round($this->price * (1 + ($rate / 100)), 2);
        } else {
            $this->calculated_price = 0;
        }
    }

    /* ----------------- DRAFT (session) ----------------- */
    private function snapshot(): array
    {
        return [
            'category_id'      => $this->category_id,
            'item_type'        => $this->item_type,
            'name'             => $this->name,
            'short_name'       => $this->short_name,
            'code'             => $this->code,
            'description'      => $this->description,
            'price'            => $this->price,
            'tax_id'           => $this->tax_id,
            'is_tax_inclusive' => $this->is_tax_inclusive,
            'variants'         => $this->variants,
            'addons'           => $this->addons,
            'picked_image_url' => $this->picked_image_url,
        ];
    }

    private function saveDraft(): void
    {
        session()->put('item_form_draft', $this->snapshot());
    }

    public function clearPickedImage(): void
    {
        $this->picked_image_url = null;
        $this->saveDraft();
    }

    /* ----------------- Open File Manager ----------------- */
    public function openPicker()
    {
        // 1) Save current form state
        $this->saveDraft();

        // 2) Go to File Manager in "picker" mode, and return to this page
        return redirect()->route('fm.view', [
            'picker' => 1,
            'return' => route('restaurant.items.create'),
        ]);
    }

    /* ----------------- SUBMIT ----------------- */
    public function submit()
    {
        if (setting('category_module')) {
            $this->validate(['category_id' => 'required|exists:categories,id']);
        }

        $this->validate([
            'name' => 'required',
            'item_type' => 'required',
            'short_name' => 'nullable|unique:items,short_name,null,id,restaurant_id,' . $this->restaurant->id,
            'code' => 'nullable|unique:items,code,null,id,restaurant_id,' . $this->restaurant->id,
            'description' => 'nullable',
            'price' => 'required|numeric',
            'tax_id' => 'nullable|exists:tax_settings,id',
            'is_tax_inclusive' => 'nullable|boolean',
        ]);

        $exists = \App\Models\Item::where([
            'restaurant_id' => $this->restaurant->id,
            'category_id'   => $this->category_id,
            'name'          => $this->name,
        ])->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'name' => 'An item with the same name already exists in this category.',
            ]);
        }

        $item = \App\Models\Item::create([
            'restaurant_id'   => $this->restaurant->id,
            'category_id'     => $this->category_id ?? null,
            'name'            => $this->name,
            'item_type'       => $this->item_type,
            'short_name'      => $this->short_name,
            'code'            => $this->code ?? null,
            'description'     => $this->description,
            'price'           => $this->price,
            'tax_id'          => $this->tax_id,
            'is_tax_inclusive'=> $this->is_tax_inclusive,
            'image_url'       => $this->picked_image_url,
        ]);

        foreach ($this->variants as $v) {
            if (!empty($v['name']) && $v['price'] !== '') {
                $item->variants()->create(['name' => $v['name'], 'price' => $v['price']]);
            }
        }

        foreach ($this->addons as $a) {
            if (!empty($a['name']) && $a['price'] !== '') {
                $item->addons()->create(['name' => $a['name'], 'price' => $a['price']]);
            }
        }

        session()->forget('item_form_draft');

        return redirect()->route('restaurant.items.index')
            ->with('success', 'Item created successfully.');
    }
}
