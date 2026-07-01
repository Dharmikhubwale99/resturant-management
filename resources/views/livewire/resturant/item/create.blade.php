<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="p-6 bg-white rounded shadow max-w-3xl w-full">
        <h2 class="text-2xl font-bold mb-6 text-center">Add Item</h2>
        <x-form.error />
        <form wire:submit.prevent="submit" class="space-y-4" enctype="multipart/form-data">

            @if (setting('category_module'))
                <x-form.select name="category_id" label="Category" wireModel="category_id" required :options="$categories" />
            @endif

            <x-form.select name="item_type" label="Item Type" wire:model="item_type" :options="$itemTypes" required />

            <x-form.input name="name" label="Name" wireModel="name" placeholder="Enter name" required />

            <x-form.input name="short_name" label="Short Name" wireModel="short_name" placeholder="Enter short name" />

            <x-form.input name="code" label="Code" wireModel="code" placeholder="Enter code" />

            <x-form.input name="price" label="Price" wireModel="price" required placeholder="Enter price"
                type="number" step="0.01" />
            <span>
                @if (!is_null($is_tax_inclusive) && $tax_id && is_numeric($price))
                    @php
                        $formatted = number_format($calculated_price, 2);
                    @endphp
                    @if ($is_tax_inclusive)
                        <strong>With Out:</strong> ₹{{ $formatted }}
                    @else
                        <strong>With tax:</strong> ₹{{ $formatted }}
                    @endif
                @endif
            </span>


            <x-form.select name="tax_id" label="GST Rate" wireModelLive="tax_id" :options="$taxOptions"
                placeholder="Select GST Rate" />

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Prices With/Without Tax?
                </label>
                <div class="flex space-x-4">
                    <label class="inline-flex items-center">
                        <input type="radio" wire:model.live="is_tax_inclusive" value="1"
                            class="form-radio text-blue-600" />
                        <span class="ml-2">With Tax (Inclusive)</span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="radio" wire:model.live="is_tax_inclusive" value="0"
                            class="form-radio text-blue-600" />
                        <span class="ml-2">Without Tax (Exclusive)</span>
                    </label>
                </div>
                @error('is_tax_inclusive')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <x-form.input name="description" label="Description" type="textarea" wireModel="description"
                placeholder="Enter description" />

            {{-- <div class="mt-3">
                <button type="button" class="px-3 py-2 bg-gray-200 rounded"
                    x-on:click="window.open('{{ url(config('lfm.url_prefix')) }}?type=image','fm','width=1200,height=600')">
                    Choose from File Manager
                </button>
            </div>

            @if ($picked_image_url)
                <div class="mt-2">
                    <img src="{{ $picked_image_url }}" class="w-20 h-20 object-cover rounded" />
                    <button type="button" class="text-red-500" x-on:click="$wire.set('picked_image_url', null)">
                        Remove
                    </button>
                </div>
            @endif --}}

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Image</label>

                <div class="flex items-center gap-3">
                    <button
                    type="button"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-1.5 rounded"
                    wire:click="openPicker"
                  >
                    Choose from File Manager
                  </button>



                    @if ($picked_image_url)
                        <img src="{{ $picked_image_url }}" class="w-16 h-16 object-cover rounded border" />
                        <button type="button" class="text-red-500"
                            wire:click="$set('picked_image_url', null)">Remove</button>
                    @endif
                </div>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Variants</label>
                @foreach ($variants as $index => $variant)
                    <div class="flex gap-2 mb-2">
                        <input type="text" wire:model="variants.{{ $index }}.name" placeholder="Variant Name"
                            class="border rounded px-2 py-1" />
                        <input type="number" wire:model="variants.{{ $index }}.price" placeholder="Price"
                            class="border rounded px-2 py-1" step="0.01" />
                        <button type="button" wire:click="removeVariant({{ $index }})"
                            class="text-red-500">Remove</button>
                    </div>
                @endforeach
                <button type="button" wire:click="addVariant" class="bg-blue-500 text-white px-2 py-1 rounded">+
                    Variant</button>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Addons</label>
                @foreach ($addons as $index => $addon)
                    <div class="flex gap-2 mb-2">
                        <input type="text" wire:model="addons.{{ $index }}.name" placeholder="Addon Name"
                            class="border rounded px-2 py-1" />
                        <input type="number" wire:model="addons.{{ $index }}.price" placeholder="Price"
                            class="border rounded px-2 py-1" step="0.01" />
                        <button type="button" wire:click="removeAddon({{ $index }})"
                            class="text-red-500">Remove</button>
                    </div>
                @endforeach
                <button type="button" wire:click="addAddon" class="bg-green-500 text-white px-2 py-1 rounded">+
                    Addon</button>
            </div>

            <div class="flex flex-row text-center  space-x-3">
                <x-form.button type="submit" title="Save" wireTarget="submit" />
                <x-form.button title="Back" class="bg-gray-500 hover:bg-gray-600 text-white"
                    route="restaurant.items.index" />
            </div>
        </form>
    </div>
</div>
{{-- @push('scripts')
<script>
    window.SetUrl = function (items) {
        const url = items[0]?.url || null;
        window.Livewire.find(@this.__instance.id).set('picked_image_url', url);
    }
  </script>
@endpush --}}
