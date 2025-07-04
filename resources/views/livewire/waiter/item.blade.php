<div class="bg-gray-100 font-sans">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center space-x-4">
                <button class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div class="flex items-center space-x-2">
                    <img src="https://via.placeholder.com/40x40/FF6B6B/FFFFFF?text=P" alt="PetPooja"
                        class="w-10 h-10 rounded">
                    <span class="text-xl font-semibold text-gray-800">PetPooja</span>
                </div>
                <button class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    New Order
                </button>
                <div class="flex items-center space-x-2">
                    <i class="fas fa-search text-gray-400"></i>
                    <input type="text" placeholder="Bill No"
                        class="border-none outline-none text-gray-600 bg-transparent">
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2 bg-gray-100 px-3 py-2 rounded">
                    <i class="fas fa-phone text-gray-500"></i>
                    <span class="text-sm text-gray-600">Call For Support</span>
                    <span class="text-sm font-medium">9099912483</span>
                </div>
                <div class="flex items-center space-x-3">
                    <button class="text-gray-600 hover:text-gray-800"><i class="fas fa-print text-lg"></i></button>
                    <button class="text-gray-600 hover:text-gray-800"><i class="fas fa-calculator text-lg"></i></button>
                    <button class="text-gray-600 hover:text-gray-800"><i class="fas fa-th text-lg"></i></button>
                    <button class="text-gray-600 hover:text-gray-800"><i class="fas fa-clock text-lg"></i></button>
                    <button class="text-gray-600 hover:text-gray-800"><i class="fas fa-bell text-lg"></i></button>
                    <button class="text-gray-600 hover:text-gray-800"><i class="fas fa-user text-lg"></i></button>
                    <button class="text-gray-600 hover:text-gray-800"><i class="fas fa-power-off text-lg"></i></button>
                </div>
            </div>
        </div>
    </header>
    <div class="flex h-screen">
        <!-- Sidebar -->
        <!-- Sidebar with Dynamic Petpooja-style Category List -->
        @if (setting('category_module'))
            <div class="w-48 bg-gray-800 text-white">
                <div class="p-4">
                    <div class="text-sm text-gray-400 mb-2">Categories</div>
                </div>
                <nav class="mt-4">
                    <button wire:click="clearCategory"
                        class="block w-full text-left px-4 py-3 {{ $selectedCategory === null ? 'bg-hub-primary text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        <i class="fas fa-list mr-2"></i>All Items
                    </button>
                    @foreach ($categories as $category)
                        <button wire:click="selectCategory({{ $category->id }})"
                            class="block w-full text-left px-4 py-3 flex items-center gap-2 {{ $selectedCategory === $category->id ? 'bg-hub-primary text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                            <span>{{ $category->name }}</span>
                        </button>
                    @endforeach
                </nav>
            </div>
        @endif


        <!-- Main Content -->
        <div class="flex-1 flex">
            <!-- Menu Items -->
            <div class="flex-1 flex flex-col gap-4">
                <input type="text" wire:model.live="search" placeholder="Search product..."
                    class="border px-3 py-1 rounded w-1/3">
                <x-form.error />
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4">
                    @forelse ($filteredItems as $item)
                        <div wire:click="itemClicked({{ $item->id }})"
                            class="relative bg-white p-2 rounded shadow hover:shadow-md transition
               border-2 {{ $item->type_color_class }}">
                            {{-- Veg/Non-veg dot/icon --}}
                            <span
                                class="absolute top-1 right-1 w-3 h-3 rounded-full
                     {{ $item->type_dot_class }}"></span>

                            <img src="{{ $item->getFirstMediaUrl('images') ?: asset('icon/hubwalelogopng.png') }}"
                                class="w-full h-28 object-cover rounded mb-2" alt="{{ $item->name }}">
                            <h3 class="text-sm font-semibold text-center">{{ $item->name }}</h3>
                            <p class="text-center text-blue-700 font-bold text-sm mt-1">
                                ₹{{ number_format($item->price, 2) }}
                            </p>
                        </div>
                    @empty
                        <p class="flex items-center col-span-full text-gray-500 text-center">
                            No items found in this category.
                        </p>
                        …
                    @endforelse
                </div>
            </div>

            <div class="w-1/3 bg-white p-4 rounded shadow h-full flex flex-col">
                <button wire:click="showTables" class="bg-blue-600 text-white px-4 py-2 rounded mb-4">
                    Table
                </button>
                <x-form.select name="order_type" label="Order Type" wire:model="order_type" :options="$orderTypes"
                    required />
                <h2 class="text-lg font-bold mb-4 text-center">Cart</h2>

                @if (count($cartItems))
                    <div class="flex-1 overflow-y-auto space-y-3">
                        @foreach ($cartItems as $key => $row)
                            @if (in_array($key, $originalKotItemKeys))
                                <div class="border rounded p-2 flex items-center justify-between bg-gray-50"
                                    wire:key="row-{{ $row['id'] }}">
                                    <div>
                                        <p class="font-semibold flex items-center gap-2">
                                            {{ $row['name'] }}
                                            <span class="text-[10px] bg-gray-300 text-gray-700 px-1.5 py-0.5 rounded">
                                                EXISTING
                                            </span>
                                        </p>
                                        <p class="text-xs text-gray-500">
                                            ₹{{ number_format($row['price'], 2) }} × {{ $row['qty'] }}
                                        </p>
                                        <p class="text-xs text-green-600 font-semibold">
                                            = ₹{{ number_format($row['price'] * $row['qty'], 2) }}
                                        </p>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                        <hr class="my-2 border-t">
                        <p class="text-sm font-semibold text-blue-600">New Items</p>

                        @foreach ($cartItems as $key => $row)
                            @if (!in_array($key, $originalKotItemKeys))
                                <div class="border rounded p-2 flex items-center justify-between"
                                    wire:key="row-{{ $row['id'] }}">
                                    <div>
                                        <p class="font-semibold flex items-center gap-2">
                                            {{ $row['name'] }}
                                            <span
                                                class="text-[10px] bg-yellow-200 text-yellow-800 px-1.5 py-0.5 rounded">
                                                NEW
                                            </span>
                                        </p>
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

                                        <button class="text-red-500 text-sm"
                                            wire:click="remove('{{ $row['id'] }}')">✕</button>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    @if (count($cartItems))
                        <div class="border-t pt-4 mt-4 space-y-2">
                            <div class="mt-4 p-3 bg-gray-50 rounded">
                                <div class="flex items-center space-x-2 mb-2">
                                    <button class="bg-red-500 text-white px-3 py-1 rounded text-sm">Bogo Offer</button>
                                    <button class="bg-gray-300 text-gray-700 px-3 py-1 rounded text-sm">Split</button>
                                    <label class="flex items-center space-x-1">
                                        <input type="checkbox" class="form-checkbox">
                                        <span class="text-sm">Complimentary</span>
                                    </label>
                                </div>
                                <div class="text-right">
                                    <div class="text-xl font-bold"> Total: ₹{{ number_format($cartTotal, 2) }}</div>
                                </div>
                            </div>

                            <div class="p-4 border-t">
                                <div class="flex space-x-2">
                                    <button
                                        class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Save</button>
                                    <button class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Save &
                                        Print</button>
                                    <button class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Save &
                                        eBill</button>
                                        @if ($editMode)
                                        <button class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700"
                                                wire:click="updateOrder">
                                            Update KOT
                                        </button>
                                    @else
                                        <button wire:click="placeOrder"
                                                class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">
                                            Kot Order
                                        </button>
                                    @endif
                                    <button class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700"
                                        wire:click="placeOrderAndPrint">KOT &
                                        Print</button>
                                </div>
                            </div>
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
                        <h3 class="text-lg font-bold mb-4">Select Variant <span
                                class="text-sm font-normal">(optional)</span>
                        </h3>

                        <div class="space-y-2 mb-6">
                            @foreach ($variantOptions as $opt)
                                <label class="flex items-center gap-2">
                                    <input type="radio" wire:model="selectedVariantId"
                                        value="{{ $opt['id'] }}">
                                    <span>
                                        {{ $opt['variant_name'] }} — ₹{{ number_format($opt['combined_price'], 2) }}
                                    </span>
                                </label>
                            @endforeach
                        </div>

                        <div class="flex justify-end gap-2">
                            <button class="px-4 py-2 bg-gray-200 rounded"
                                wire:click="$set('showVariantModal', false)">Cancel</button>

                            <button class="px-4 py-2 bg-indigo-600 text-white rounded"
                                wire:click="addSelectedVariant">
                                Add to Cart
                            </button>
                        </div>
                    </div>
                </div>
            @endif


            @if ($showNoteModal)
                <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
                    <div class="bg-white p-6 rounded shadow w-full max-w-md">
                        <h2 class="text-lg font-bold mb-4">Add Note</h2>
                        <textarea wire:model.defer="noteInput" rows="4" class="w-full border rounded p-2"
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

            @if ($showTableList)
                <div class="fixed inset-0 bg-black bg-opacity-30 flex items-center justify-center z-50">
                    <div class="bg-white p-6 rounded shadow-lg max-w-md w-full">
                        <h2 class="text-lg font-bold mb-4">Occupied Tables</h2>
                        <ul>
                            @foreach ($occupiedTables as $table)
                                <li>
                                    <button wire:click="selectTable({{ $table->id }})"
                                        class="block w-full text-left px-4 py-2 hover:bg-blue-100 rounded">
                                        {{ $table->name }} ({{ $table->area->name ?? '' }})
                                    </button>
                                </li>
                            @endforeach
                        </ul>
                        <button wire:click="$set('showTableList', false)"
                            class="mt-4 bg-gray-500 text-white px-4 py-2 rounded">Close</button>
                    </div>
                </div>
            @endif

            @if ($selectedTable)
                <div class="mt-6">
                    <h3 class="text-lg font-bold mb-2">Orders for Table: {{ $selectedTable->name }}</h3>
                    @if ($ordersForTable->isEmpty())
                        <p>No orders for this table.</p>
                    @else
                        <ul>
                            @foreach ($ordersForTable as $order)
                                <li class="mb-2 border-b pb-2">
                                    Order #{{ $order->id }} - {{ $order->status }} - ₹{{ $order->total_amount }}
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            @endif


        </div>
    </div>
</div>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const menuItems = document.querySelectorAll('.grid > div');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    this.classList.add('ring-2', 'ring-blue-500');
                    setTimeout(() => {
                        this.classList.remove('ring-2', 'ring-blue-500');
                    }, 300);
                });
            });

            const minusButtons = document.querySelectorAll('.fa-minus');
            const plusButtons = document.querySelectorAll('.fa-plus');

            minusButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const qtySpan = this.parentElement.querySelector('span');
                    let qty = parseInt(qtySpan.textContent);
                    if (qty > 1) {
                        qtySpan.textContent = qty - 1;
                    }
                });
            });

            plusButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const qtySpan = this.parentElement.querySelector('span');
                    let qty = parseInt(qtySpan.textContent);
                    qtySpan.textContent = qty + 1;
                });
            });

            const orderTypeButtons = document.querySelectorAll('.bg-red-500, .bg-gray-300');
            orderTypeButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    orderTypeButtons.forEach(b => {
                        b.classList.remove('bg-red-500', 'text-white');
                        b.classList.add('bg-gray-300', 'text-gray-700');
                    });
                    this.classList.remove('bg-gray-300', 'text-gray-700');
                    this.classList.add('bg-red-500', 'text-white');
                });
            });
        });
    </script>


    <script>
        Livewire.on('printKot', (event) => {
            const kotId = event.kotId;
            window.open(`/waiter/kot-print/${kotId}`, '_blank');
        });
    </script>
@endpush
