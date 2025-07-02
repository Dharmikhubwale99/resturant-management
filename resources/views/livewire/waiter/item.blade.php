<div class="flex gap-4 p-4 bg-gray-100 min-h-screen">
    @if (setting('category_module'))
        <div class="w-1/5 p-2 overflow-y-auto bg-white rounded shadow h-full">
            <h2 class="text-lg font-bold mb-4 text-center">Categories</h2>
            <ul class="space-y-2">
                <li>
                    <button wire:click="clearCategory"
                            class="w-full px-3 py-2 text-left rounded {{ $selectedCategory === null ? 'bg-blue-200' : 'hover:bg-blue-100' }}">
                        All
                    </button>
                </li>
            @foreach ($categories as $category)
                <button wire:click="selectCategory({{ $category->id }})"
                    class="w-full mb-2 p-2 flex flex-col rounded
                    {{ $selectedCategory === $category->id ? 'bg-blue-200' : 'hover:bg-blue-100' }}">
                    <span class="text-sm font-medium">{{ $category->name }}</span>
                </button>
            @endforeach
        </ul>
        </div>
    @endif

    <div class="flex-1 flex flex-col gap-4">
        <input type="text" wire:model.live="search" placeholder="Search product..."
            class="border px-3 py-1 rounded w-1/3">
        <x-form.error />
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-4 gap-4">
            @forelse ($filteredItems as $item)
                <div class="bg-white p-2 rounded shadow hover:shadow-md transition"
                    wire:click="itemClicked({{ $item->id }})">
                    <img src="{{ $item->getFirstMediaUrl('images') ?: asset('icon/hubwalelogopng.png') }}"
                        alt="{{ $item->name }}" class="w-full h-28 object-fit rounded mb-2">
                    <h3 class="text-sm font-semibold text-center">{{ $item->name }}</h3>
                    <p class="text-center text-blue-700 font-bold text-sm mt-1">
                        ₹{{ number_format($item->price, 2) }}
                    </p>
                </div>
            @empty
                <p class="flex items-center col-span-full text-gray-500 text-center">
                    No items found in this category.
                </p>
            @endforelse
        </div>
    </div>

    <div class="w-1/3 bg-white p-4 rounded shadow h-full flex flex-col">
        <x-form.select name="order_type" label="Order Type" wire:model="order_type" :options="$orderTypes" required />
        <h2 class="text-lg font-bold mb-4 text-center">Cart</h2>

        @if (count($cartItems))
            <div class="flex-1 overflow-y-auto space-y-3">
                @foreach ($cartItems as $row)
                    <div class="border rounded p-2 flex items-center justify-between"
                        wire:key="row-{{ $row['id'] }}">

                        <div>
                            <p class="font-semibold">{{ $row['name'] }}</p>
                            <p class="text-xs text-gray-500">
                                ₹{{ number_format($row['price'], 2) }} × {{ $row['qty'] }}
                            </p>
                            <p class="text-xs text-green-600 font-semibold">
                                = ₹{{ number_format($row['price'] * $row['qty'], 2) }}
                            </p>
                            <button class="text-xs bg-blue-100 text-blue-700 px-2 rounded"
                            wire:click="openNoteModal('{{ $row['id'] }}')">Note</button>
                        </div>

                        <div class="flex items-center gap-2">
                            <button class="px-2 bg-gray-200 rounded"
                                wire:click="decrement('{{ $row['id'] }}')">−</button>

                            <input type="number" min="1" class="w-14 text-center border rounded"
                                wire:model.number="cart.{{ $row['id'] }}.qty"
                                wire:change="updateQty('{{ $row['id'] }}',$event.target.value)" />

                            <button class="px-2 bg-gray-200 rounded"
                                wire:click="increment('{{ $row['id'] }}')">＋</button>

                            <button class="text-red-500 text-sm" wire:click="remove('{{ $row['id'] }}')">✕</button>
                        </div>
                    </div>
                @endforeach
            </div>

            @if (count($cartItems))
            <div class="border-t pt-4 mt-4 space-y-2">
                <p class="text-right font-bold">
                    Total: ₹{{ number_format($cartTotal,2) }}
                </p>

                <button  wire:click="placeOrder"
                         class="w-full py-2 bg-green-600 text-white rounded hover:bg-green-700">
                    Place Order
                </button>
            </div>
        @endif
        @else
            <div class="flex-1 flex flex-col items-center justify-center text-gray-500">
                <p>Cart empty</p>
            </div>
        @endif
    </div>

    @if ($showVariantModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white w-full max-w-sm rounded shadow-lg p-6">
                <h3 class="text-lg font-bold mb-4">Select Variant</h3>

                <div class="space-y-2 mb-6">
                    @foreach ($variantOptions as $opt)
                        <label class="flex items-center gap-2">
                            <input type="radio" wire:model="selectedVariantId" value="{{ $opt['id'] }}">
                            <span>
                                {{ $opt['variant_name'] }}
                                — ₹{{ number_format($opt['combined_price'], 2) }}
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="flex justify-end gap-2">
                    <button class="px-4 py-2 bg-gray-200 rounded"
                        wire:click="$set('showVariantModal', false)">Cancel</button>
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded" wire:click="addSelectedVariant">Add to
                        Cart</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showNoteModal)
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded shadow w-full max-w-md">
            <h2 class="text-lg font-bold mb-4">Add Note</h2>
            <textarea wire:model.defer="noteInput"
                      rows="4"
                      class="w-full border rounded p-2"
                      placeholder="Enter special instructions..."></textarea>

            <div class="flex justify-end gap-2 mt-4">
                <button wire:click="$set('showNoteModal', false)" class="px-4 py-2 bg-gray-200 rounded">
                    Cancel
                </button>
                <button wire:click="saveNote" class="px-4 py-2 bg-blue-600 text-white rounded">
                    Save Note
                </button>
            </div>
        </div>
    </div>
@endif

</div>
