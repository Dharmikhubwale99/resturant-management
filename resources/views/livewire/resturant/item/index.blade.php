<div class="p-6 bg-white rounded shadow">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
        <h2 class="text-xl font-bold truncate">Item List</h2>

        <div class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-3">
            <x-form.input name="search" placeholder="Search..." wireModelLive="search" wrapperClass="mb-0 w-full sm:w-auto"
                inputClass="w-full sm:w-72" />

            <x-form.select name="filterItemType" wireModelLive="filterItemType" :options="['veg' => 'Veg', 'non_veg' => 'Non-Veg', 'beverage' => 'Beverage']" placeholder="All Types"
                wrapperClass="mb-0 w-full sm:w-40" inputClass="text-sm w-full" />

            <div class="flex gap-2 w-full sm:w-auto">
                @can('item-import')
                    <x-form.button title="Import" class="flex-1 sm:flex-none bg-green-600 hover:bg-green-700 text-white"
                        wire:click="$set('showImportModal', true)" />
                @endcan
                @can('item-create')
                    <x-form.button title="+ Add" route="restaurant.items.create"
                        class="flex-1 whitespace-nowrap sm:flex-none bg-blue-600 hover:bg-blue-700 text-white" />
                @endcan
            </div>
        </div>
    </div>
    <x-form.error />

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">#</th>
                    <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Image</th>
                    @if (setting('category_module'))
                        <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Category</th>
                    @endif
                    <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                    <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Item Type</th>
                    <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Short Name</th>
                    <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Price</th>
                    <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                    <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($items as $index => $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 whitespace-nowrap text-sm text-gray-900">
                            {{ $items->firstItem() + $index }}
                        </td>
                        @php
                            $displayUrl =
                                $item->image_url ?:
                                ($item->getFirstMediaUrl('images') ?:
                                asset('icon/hubwalelogopng.png'));
                        @endphp
                        <td class="px-6 whitespace-nowrap text-sm text-gray-900">
                            @can('file-manager')
                                <button type="button" class="group relative" onclick="openLfmForItem({{ $item->id }})"
                                    title="Click to change image">
                                    <img src="{{ $displayUrl }}" alt="Item Image"
                                        class="w-12 h-8 object-cover rounded ring-1 ring-gray-200 group-hover:ring-blue-400 transition" />
                                    <span
                                        class="absolute -bottom-4 left-1/2 -translate-x-1/2 text-[10px] text-blue-600 opacity-0 group-hover:opacity-100">
                                        Change
                                    </span>
                                </button>
                            @else
                                <img src="{{ $displayUrl }}" alt="Item Image" class="w-12 h-8 object-cover rounded">
                            @endcan
                        </td>

                        @if (setting('category_module'))
                            <td class="px-6 whitespace-nowrap text-sm text-gray-900">{{ $item->category->name ?? '' }}</td>
                        @endif
                        <td class="px-6 whitespace-nowrap text-sm text-gray-900">{{ $item->name }}</td>
                        <td class="px-6 whitespace-nowrap text-sm text-gray-900">{{ $item->item_type }}</td>
                        <td class="px-6 whitespace-nowrap text-sm text-gray-900">{{ $item->short_name }}</td>
                        <td class="px-6 whitespace-nowrap text-sm text-gray-900">{{ $item->price }}</td>
                        <td class="px-6 whitespace-nowrap text-sm">

                            @if ($item->is_active)
                                <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                    Inactive
                                </span>
                            @else
                                <span class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                    Active
                                </span>
                            @endif
                        </td>
                        <td class="px-2 text-sm text-gray-900">
                            <div class="flex items-center justify-start space-x-2">
                                <x-form.button title=""
                                    class=" p-1 w-5 h-10 rounded flex items-center justify-center mt-3"
                                    wireClick="confirmBlock({{ $item->id }})">
                                    @if ($item->is_active)
                                        <span class="w-5 h-1 flex items-center justify-center">
                                            {!! file_get_contents(public_path('icon/xmark.svg')) !!} </span>
                                    @else
                                        <span class="w-5 h-1 flex items-center justify-center">
                                            {!! file_get_contents(public_path('icon/check.svg')) !!} </span>
                                    @endif
                                </x-form.button>
                                @can('item-show')
                                    <x-form.button title="" class="w-8 h-8 rounded flex items-center justify-center"
                                        :route="['restaurant.items.show', $item->id]">
                                        <span class="w-4 h-4 text-yellow-700">
                                            {!! file_get_contents(public_path('icon/view.svg')) !!}
                                        </span>
                                    </x-form.button>
                                @endcan
                                @can('item-edit')
                                    <x-form.button title="" class="w-8 h-8 rounded flex items-center justify-center"
                                        :route="['restaurant.items.edit', $item->id]">
                                        <span class="w-4 h-4">
                                            {!! file_get_contents(public_path('icon/edit.svg')) !!}
                                        </span>
                                    </x-form.button>
                                @endcan
                                @can('item-delete')
                                    <x-form.button title="" class="w-8 h-8 rounded flex items-center justify-center"
                                        wireClick="confirmDelete({{ $item->id }})">
                                        <span class="w-4 h-4">
                                            {!! file_get_contents(public_path('icon/delete.svg')) !!}
                                        </span>
                                    </x-form.button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

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
    <div class="mt-4">
        {{ $items->links() }}
    </div>
</div>

@if ($showImportModal)
    <div class="fixed inset-0 flex items-center justify-center z-50 bg-black bg-opacity-30">
        <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-md relative">
            <button class="absolute top-2 right-2 text-gray-500" wire:click="$set('showImportModal', false)">✕</button>
            <h3 class="text-lg font-bold mb-2">Import Items from Excel</h3>
            @if (setting('category_module'))
                <p class="mb-2 text-sm text-gray-600">Required columns: <b>category_name, name, item_type, price</b>
                </p>
                <a href="{{ asset('sample_items_import_with_category.xlsx') }}"
                    class="text-blue-600 underline text-xs mt-2 inline-block">Download Sample Excel</a>
            @else
                <p class="mb-2 text-sm text-gray-600">Required columns: <b>name, item_type, price</b></p>
                <a href="{{ asset('sample_items_import_without_category.xlsx') }}"
                    class="text-blue-600 underline text-xs mt-2 inline-block">Download Sample Excel</a>
            @endif
            <form wire:submit.prevent="importItems" enctype="multipart/form-data" class="space-y-3">
                <div class="relative">
                    <input type="file" wire:model="importFile" accept=".xlsx,.xls"
                        class="border rounded px-2 py-1 w-full" />
                    <div wire:loading wire:target="importFile"
                        class="absolute inset-0 flex items-center justify-center bg-white bg-opacity-70 rounded">
                        <svg class="animate-spin h-5 w-5 text-green-600" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"></path>
                        </svg>
                        <span class="ml-2 text-green-700 text-xs">Uploading...</span>
                    </div>
                </div>
                @error('importFile')
                    <span class="text-red-500 text-xs">{{ $message }}</span>
                @enderror
                @if ($importErrors)
                    <div class="bg-red-100 text-red-700 p-2 rounded text-xs">
                        <ul>
                            @foreach ($importErrors as $err)
                                <li>Row {{ $err['row'] }}: {{ $err['error'] }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <div class="flex justify-end gap-2">
                    {{-- <button type="button" wire:click="$set('showImportModal', false)" class="px-3 py-1 bg-gray-300 rounded">Cancel</button> --}}
                    {{-- <button type="submit" class="px-3 py-1 bg-green-600 text-white rounded">Import</button> --}}
                    <x-form.button type="submit" title="Save" wireTarget="submit" />
                    <x-form.button title="Back" class="bg-gray-500 hover:bg-gray-600 text-white"
                        route="restaurant.items.index" />
                </div>
            </form>
        </div>
    </div>
@endif
@if ($confirmingBlock)
    <div class="fixed inset-0 bg-transparent bg-opacity-0 z-40 flex items-center justify-center">
        <div class="bg-white rounded-lg p-6 shadow-xl z-50 w-full max-w-md">
            <h3 class="text-lg font-semibold mb-4 text-yellow-600">
                {{ optional(\App\Models\Item::find($itemId))->is_active ? 'Confirm Unblock' : 'Confirm Block' }}
            </h3>
            <p class="text-gray-700 mb-6">
                Are you sure you want to
                {{ optional(\App\Models\Item::find($itemId))->is_active ? 'unblock' : 'block' }} this
                Item?
            </p>

            <div class="flex justify-end space-x-3">
                <button wire:click="cancelBlock"
                    class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-gray-700">Cancel</button>
                <button wire:click="toggleBlock"
                    class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                    {{ optional(\App\Models\Item::find($itemId))->is_active ? 'UnBlock' : 'Block' }}
                </button>
            </div>
        </div>
    </div>
@endif
</div>
@push('scripts')
    <script>
        function openLfmForItem(itemId) {
            window.__lfmActiveItemId = itemId;

            const urlPrefix = @json(url(config('lfm.url_prefix', 'file-manager')));
            const w = Math.min(1200, window.innerWidth - 40);
            const h = Math.min(600, window.innerHeight - 80);
            const left = (window.innerWidth - w) / 2;
            const top = (window.innerHeight - h) / 2;

            window.open(`${urlPrefix}?type=image`, 'fm',
                `width=${w},height=${h},left=${left},top=${top},resizable=yes,scrollbars=yes`);
        }


        window.SetUrl = function(items) {
            const url = items && items[0] ? items[0].url : null;
            const itemId = window.__lfmActiveItemId;

            if (!url || !itemId) return;


            Livewire.dispatch('fileSelected', {
                itemId: itemId,
                url: url
            });


            window.__lfmActiveItemId = null;
        };
    </script>
@endpush
