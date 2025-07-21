<div class="p-6 bg-white rounded shadow">
    <div class="p-6 bg-white rounded shadow">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Item List</h2>
            <div class="flex items-center gap-4">
                <x-form.input name="search" placeholder="Search..." wireModelLive="search" wrapperClass="mb-0"
                    inputClass="w-72" />

                <x-form.select name="filterItemType" wireModelLive="filterItemType" :options="[
                    'veg' => 'Veg',
                    'non_veg' => 'Non-Veg',
                    'beverage' => 'Beverage',
                ]"
                    placeholder="All Types" wrapperClass="mb-0" inputClass="text-sm" />


                <x-form.button title="Import Excel"
                    class="bg-green-600 hover:bg-green-700 text-white"
                    wire:click="$set('showImportModal', true)" />
                <x-form.button title="+ Add" route="restaurant.items.create"
                    class="bg-blue-600 hover:bg-blue-700 text-white" />
            </div>

        </div>
        <x-form.error />
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">#</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Image</th>
                        @if (setting('category_module'))
                            <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Category</th>
                        @endif
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Item Type</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Short Name</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Price</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($items as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 text-sm text-gray-900">{{ $loop->iteration }}</td>
                            @php
                                $imgUrl = $item->getFirstMediaUrl('images') ?: asset('icon/hubwalelogopng.png');
                            @endphp
                            <td class="px-6 text-sm text-gray-900">
                                <img src="{{ $imgUrl }}" alt="Item Image" class="w-12 h-8 object-cover rounded">
                            </td>
                            @if (setting('category_module'))
                                <td class="px-6 text-sm text-gray-900">{{ $item->category->name ?? '' }}</td>
                            @endif
                            <td class="px-6 text-sm text-gray-900">{{ $item->name }}</td>
                            <td class="px-6 text-sm text-gray-900">{{ $item->item_type }}</td>
                            <td class="px-6 text-sm text-gray-900">{{ $item->short_name }}</td>
                            <td class="px-6 text-sm text-gray-900">{{ $item->price }}</td>
                            <td class="px-2 text-sm text-gray-900">
                                <div class="flex items-center justify-start space-x-2">
                                    <x-form.button title=""
                                        class="w-8 h-8 rounded flex items-center justify-center" :route="['restaurant.items.show', $item->id]">
                                        <span class="w-4 h-4 text-yellow-700">
                                            {!! file_get_contents(public_path('icon/view.svg')) !!}
                                        </span>
                                    </x-form.button>
                                    <x-form.button title=""
                                        class="w-8 h-8 rounded flex items-center justify-center" :route="['restaurant.items.edit', $item->id]">
                                        <span class="w-4 h-4">
                                            {!! file_get_contents(public_path('icon/edit.svg')) !!}
                                        </span>
                                    </x-form.button>


                                    <x-form.button title=""
                                        class="w-8 h-8 rounded flex items-center justify-center"
                                        wireClick="confirmDelete({{ $item->id }})">
                                        <span class="w-4 h-4">
                                            {!! file_get_contents(public_path('icon/delete.svg')) !!}
                                        </span>
                                    </x-form.button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="mt-4">
                {{ $items->links() }}
            </div>

            @if ($confirmingDelete)
                <div class="fixed inset-0 bg-transparent bg-opacity-0 z-40 flex items-center justify-center">
                    <div class="bg-white rounded-lg p-6 shadow-xl z-50 w-full max-w-md">
                        <h3 class="text-lg font-semibold mb-4 text-red-600">Confirm Delete</h3>
                        <p class="text-gray-700 mb-6">Are you sure you want to delete this item? This action cannot be
                            undone.</p>

                        <div class="flex justify-end space-x-3">
                            <button wire:click="cancelDelete"
                                class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-gray-700">Cancel</button>
                            <button wire:click="deleteItem({{ $item->id }})"
                                class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Delete</button>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    @if($showImportModal)
    <div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-30">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md relative">
            <button class="absolute top-2 right-2 text-gray-500" wire:click="$set('showImportModal', false)">✕</button>
            <h3 class="text-lg font-bold mb-2">Import Items from Excel</h3>
            <p class="mb-2 text-sm text-gray-600">Required columns: <b>category_name, name, item_type, price</b></p>
            <form wire:submit.prevent="importItems" enctype="multipart/form-data" class="space-y-3">
                <input type="file" wire:model="importFile" accept=".xlsx,.xls" class="border rounded px-2 py-1 w-full" />
                @error('importFile') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                @if($importErrors)
                    <div class="bg-red-100 text-red-700 p-2 rounded text-xs">
                        <ul>
                            @foreach($importErrors as $err)
                                <li>Row {{ $err['row'] }}: {{ $err['error'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="$set('showImportModal', false)" class="px-3 py-1 bg-gray-300 rounded">Cancel</button>
                    <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded">Import</button>
                </div>
            </form>
            <a href="{{ asset('sample_items_import.xlsx') }}" class="text-blue-600 underline text-xs mt-2 inline-block">Download Sample Excel</a>
        </div>
    </div>
    @endif
</div>
