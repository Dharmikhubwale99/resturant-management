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

            <x-form.input name="description" label="Description" type="textarea" wireModel="description"
                placeholder="Enter description" />

            <x-form.input label="Images" name="images" type="file" wireModel="images" multiple />
            @if ($images)
                <div class="flex gap-2 mt-2">
                    @foreach ($images as $image)
                        <div class="relative w-20 h-20">
                            <img src="{{ $image->temporaryUrl() }}" alt="Preview" class="w-20 h-20 object-cover rounded" />
                            <div wire:loading wire:target="images" class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-60 rounded">
                                <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                                </svg>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Variants</label>
                @foreach ($variants as $index => $variant)
                    <div class="flex gap-2 mb-2">
                        <input type="text" wire:model="variants.{{ $index }}.name" placeholder="Variant Name" class="border rounded px-2 py-1" />
                        <input type="number" wire:model="variants.{{ $index }}.price" placeholder="Price" class="border rounded px-2 py-1" step="0.01" />
                        <button type="button" wire:click="removeVariant({{ $index }})" class="text-red-500">Remove</button>
                    </div>
                @endforeach
                <button type="button" wire:click="addVariant" class="bg-blue-500 text-white px-2 py-1 rounded">+ Variant</button>
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Addons</label>
                @foreach ($addons as $index => $addon)
                    <div class="flex gap-2 mb-2">
                        <input type="text" wire:model="addons.{{ $index }}.name" placeholder="Addon Name" class="border rounded px-2 py-1" />
                        <input type="number" wire:model="addons.{{ $index }}.price" placeholder="Price" class="border rounded px-2 py-1" step="0.01" />
                        <button type="button" wire:click="removeAddon({{ $index }})" class="text-red-500">Remove</button>
                    </div>
                @endforeach
                <button type="button" wire:click="addAddon" class="bg-green-500 text-white px-2 py-1 rounded">+ Addon</button>
            </div>

            <div class="flex flex-row text-center  space-x-3">
                <x-form.button type="submit" title="Save" wireTarget="submit" />
                <x-form.button title="Back" class="bg-gray-500 hover:bg-gray-600 text-white"
                    route="restaurant.items.index" />
            </div>
        </form>
    </div>
</div>
