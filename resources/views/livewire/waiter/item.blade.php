<div class="font-sans bg-gray-100 min-h-screen">
    <!-- Header - Responsive -->
    <header class="bg-white shadow-sm border-b">
        <div class="flex flex-col md:flex-row items-center justify-between px-2 md:px-4 py-2 md:py-3">
            <!-- Top Row (Mobile) -->
            <div class="flex items-center justify-between w-full md:w-auto mb-2 md:mb-0">
                <div class="flex items-center space-x-2">
                    <button class="text-gray-600 hover:text-gray-800 md:hidden">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <div class="flex items-center space-x-2">
                        <img src="https://via.placeholder.com/40x40/FF6B6B/FFFFFF?text=P" alt="PetPooja"
                            class="w-8 h-8 md:w-10 md:h-10 rounded">
                        <span class="text-lg md:text-xl font-semibold text-gray-800">PetPooja</span>
                    </div>
                </div>
                <div class="flex items-center space-x-2 md:hidden">
                    <button class="text-gray-600 hover:text-gray-800"><i class="fas fa-user text-lg"></i></button>
                    <button class="text-gray-600 hover:text-gray-800"><i class="fas fa-power-off text-lg"></i></button>
                </div>
            </div>

            <!-- Middle Row (Mobile) -->
            <div class="flex items-center w-full md:w-auto mb-2 md:mb-0">
                <button
                    class="bg-red-500 text-white px-3 py-1 md:px-4 md:py-2 rounded hover:bg-red-600 text-sm md:text-base">
                    New Order
                </button>
                <div class="flex items-center space-x-2 ml-2 flex-1 md:flex-none">
                    <i class="fas fa-search text-gray-400"></i>
                    <input type="text" placeholder="Bill No"
                        class="border-none outline-none text-gray-600 bg-transparent w-full md:w-32">
                </div>
            </div>

            <!-- Bottom Row (Desktop) -->
            <div class="hidden md:flex items-center space-x-4">
                <div class="flex items-center space-x-2 bg-gray-100 px-3 py-2 rounded">
                    <i class="fas fa-phone text-gray-500"></i>
                    <span class="text-sm text-gray-600">Support</span>
                    <span class="text-sm font-medium">9099912483</span>
                </div>
                <div class="flex items-center space-x-3">
<!-- Pending KOT  (PDF icon) -->
<a href="{{ route('waiter.kots.pending') }}"
   class="text-gray-600 hover:text-gray-800"
   title="Pending KOT Orders">
    <i class="fas fa-file-pdf text-lg"></i>
</a>
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

    <div class="flex flex-col md:flex-row h-full">
        <!-- Sidebar - Responsive -->
        @if (setting('category_module'))
            <div class="w-full md:w-48 bg-gray-800 text-white flex-shrink-0 overflow-y-auto">
                <div class="p-2 md:p-4 flex justify-between items-center">
                    <div class="text-sm text-gray-400">Categories</div>
                    <!-- Mobile expand button (hidden on desktop) -->
                    <button class="md:hidden text-gray-300 hover:text-white focus:outline-none"
                        onclick="document.querySelector('.category-nav').classList.toggle('hidden')">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>

                <!-- Navigation with expandable categories -->
                <nav class="category-nav hidden md:block mb-4">
                    <button wire:click="clearCategory"
                        class="block w-full text-left px-3 py-2 md:px-4 md:py-3 text-sm md:text-base {{ $selectedCategory === null ? 'bg-hub-primary text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                        <i class="fas fa-list mr-2"></i>All Items
                    </button>

                    <!-- Visible categories (first 5 on mobile) -->
                    @foreach ($categories->take(5) as $category)
                        <button wire:click="selectCategory({{ $category->id }})"
                            class="block w-full text-left px-3 py-2 md:px-4 md:py-3 flex items-center gap-2 text-sm md:text-base {{ $selectedCategory === $category->id ? 'bg-hub-primary text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                            <span>{{ $category->name }}</span>
                        </button>
                    @endforeach

                    <!-- Hidden categories (shown when expanded on mobile) -->
                    <div class="mobile-categories hidden md:hidden">
                        @foreach ($categories->slice(5) as $category)
                            <button wire:click="selectCategory({{ $category->id }})"
                                class="block w-full text-left px-3 py-2 md:px-4 md:py-3 flex items-center gap-2 text-sm md:text-base {{ $selectedCategory === $category->id ? 'bg-hub-primary text-white' : 'text-gray-300 hover:bg-gray-700' }}">
                                <span>{{ $category->name }}</span>
                            </button>
                        @endforeach
                    </div>

                    <!-- Show More/Less toggle for mobile -->
                    <button
                        class="md:hidden w-full text-left px-3 py-2 text-gray-300 hover:text-white flex items-center gap-2 text-sm"
                        onclick="document.querySelector('.mobile-categories').classList.toggle('hidden'); this.querySelector('span').textContent = this.querySelector('span').textContent === 'Show More +' ? 'Show Less -' : 'Show More +'">
                        <i class="fas fa-chevron-down"></i>
                        <span>Show More +</span>
                    </button>
                </nav>
            </div>
        @endif


        <!-- Main Content - Responsive -->
        <div class="flex-1 flex flex-col md:flex-row overflow-hidden">
            <!-- Menu Items - Responsive -->
            <div class="flex-1 p-2 md:p-4 overflow-y-auto">
                <div class="mb-4">
                    <input type="text" wire:model.live="search" placeholder="Search product..."
                        class="border px-3 py-1 rounded w-full md:w-1/3">
                    <x-form.error />
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-5 gap-2 md:gap-4">
                    @forelse ($filteredItems as $item)
                        <div wire:click="itemClicked({{ $item->id }})"
                            class="relative bg-white p-1 md:p-2 rounded shadow hover:shadow-md transition
               border-2 {{ $item->type_color_class }} cursor-pointer">
                            <!-- Veg/Non-veg dot/icon -->
                            <span
                                class="absolute top-1 right-1 w-2 h-2 md:w-3 md:h-3 rounded-full
                     {{ $item->type_dot_class }}"></span>

                            <img src="{{ $item->getFirstMediaUrl('images') ?: asset('icon/hubwalelogopng.png') }}"
                                class="w-full h-20 md:h-28 object-cover rounded mb-1 md:mb-2"
                                alt="{{ $item->name }}">
                            <h3 class="text-xs md:text-sm font-semibold text-center truncate px-1">{{ $item->name }}
                            </h3>
                            <p class="text-center text-blue-700 font-bold text-xs md:text-sm mt-1">
                                ₹{{ number_format($item->price, 2) }}
                            </p>
                        </div>
                    @empty
                        <p class="flex items-center col-span-full text-gray-500 text-center py-4">
                            No items found in this category.
                        </p>
                    @endforelse
                </div>
            </div>

            <!-- Cart Section - Responsive -->
            <div
                class="w-full md:w-2/5 lg:w-1/3 bg-white p-2 md:p-4 rounded shadow flex flex-col border-t lg:border-t-0 lg:border-l border-gray-200">
                <!-- Order Type Buttons -->
                <div class="flex flex-wrap gap-1 md:gap-2 mb-2 md:mb-4">
                    @php
                        $opts = ['dine_in' => 'Dine In', 'delivery' => 'Delivery', 'pick_up' => 'Pick Up'];
                    @endphp

                    @foreach ($opts as $value => $label)
                        <button wire:click="selectOrderType('{{ $value }}')" @class([
                            'px-3 py-1 md:px-4 md:py-2 rounded text-xs md:text-sm flex-1 transition',
                            $order_type === $value
                                ? 'bg-red-500 text-white hover:bg-red-600'
                                : 'bg-gray-300 text-gray-700 hover:bg-gray-400',
                        ])>
                            {{ $label }}
                        </button>
                    @endforeach
                    @error('order_type')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <h2 class="text-md md:text-lg font-bold mb-2 md:mb-4 text-center">Cart</h2>
                @if (count($cartItems))
                    <div class="flex-1 overflow-y-auto space-y-2 md:space-y-3">
                        <p class="text-xs md:text-sm font-semibold text-blue-600">Old - #{{ $kotId ?? '-' }} •
                            {{ \Carbon\Carbon::parse($kotTime)->format('h:i') }}</p>
                        @foreach ($cartItems as $key => $row)
                            @if (in_array($key, $originalKotItemKeys) && $row['qty'] > 0)
                                <div class="border rounded p-1 md:p-2 flex items-center justify-between bg-gray-50"
                                    wire:key="row-{{ $row['id'] }}">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold flex items-center gap-1 text-xs md:text-sm truncate">
                                            {{ $row['name'] }}

                                        </p>
                                        <div class="flex items-baseline gap-2">
                                            <p class="text-xs text-gray-500 whitespace-nowrap">
                                                ₹{{ number_format($row['price'], 2) }} × {{ $row['qty'] }}
                                            </p>
                                            <p class="text-xs text-green-600 font-semibold whitespace-nowrap">
                                                = ₹{{ number_format($row['price'] * $row['qty'], 2) }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach

                        @if (count($cartItems) > count($originalKotItemKeys))
                            <hr class="my-1 md:my-2 border-t">
                            <p class="text-xs md:text-sm font-semibold text-blue-600">New -
                                {{ \Carbon\Carbon::parse(now())->format('h:i:s') }}</p>
                            @foreach ($cartItems as $key => $row)
                            @if (!in_array($key, $originalKotItemKeys) && $row['qty'] > 0)
                                    <div class="border rounded p-1 md:p-2 flex items-center justify-between"
                                        wire:key="row-{{ $row['id'] }}">
                                        <div class="flex-1 min-w-0">
                                            <p
                                                class="font-semibold flex items-center gap-1 text-xs md:text-sm truncate">
                                                {{ $row['name'] }}
                                            </p>
                                            <div class="flex items-baseline gap-2">
                                                <p class="text-xs text-gray-500 whitespace-nowrap">
                                                    ₹{{ number_format($row['price'], 2) }} × {{ $row['qty'] }}
                                                </p>
                                                <p class="text-xs text-green-600 font-semibold whitespace-nowrap">
                                                    = ₹{{ number_format($row['price'] * $row['qty'], 2) }}
                                                </p>
                                            </div>
                                            <button class="text-xs bg-blue-100 text-blue-700 px-1 md:px-2 rounded mt-1"
                                                wire:click="openNoteModal('{{ $row['id'] }}')">Note</button>
                                        </div>

                                        <div class="flex items-center gap-1 md:gap-2 ml-2">
                                            <button class="px-1 md:px-2 bg-gray-200 rounded text-xs md:text-base"
                                                wire:click="decrement('{{ $row['id'] }}')">−</button>

                                            <input type="number" min="1"
                                                class="w-10 md:w-12 text-center border rounded text-xs md:text-base"
                                                wire:model.number="cart.{{ $row['id'] }}.qty"
                                                wire:change="updateQty('{{ $row['id'] }}',$event.target.value)" />

                                            <button class="px-1 md:px-2 bg-gray-200 rounded text-xs md:text-base"
                                                wire:click="increment('{{ $row['id'] }}')">＋</button>

                                            <button class="text-red-500 text-xs md:text-sm"
                                                wire:click="remove('{{ $row['id'] }}')">✕</button>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        @endif
                    </div>

                    @if (count($cartItems))
                        <div class="border-t pt-2 md:pt-4 mt-2 md:mt-4 space-y-1 md:space-y-2">
                            <div class="flex flex-wrap items-center gap-1 md:gap-2 mb-1 md:mb-2">
                                <button class="bg-red-500 text-white px-2 py-1 rounded text-xs md:text-sm flex-1">Bogo
                                    Offer</button>
                                <button
                                    class="bg-gray-300 text-gray-700 px-2 py-1 rounded text-xs md:text-sm flex-1">Split</button>
                            </div>

                            <div class="text-right text-lg md:text-xl font-bold py-1 md:py-2">
                                Total: ₹{{ number_format($cartTotal, 2) }}
                            </div>

                            <!-- Custom Radio Group for Payment -->
                            <div class="flex flex-wrap justify-center gap-4 mt-3 mb-3">
                                @php
                                    $paymentMethods = [
                                        'cash' => 'Cash',
                                        'card' => 'Card',
                                        'due' => 'Due',
                                        'other' => 'Other',
                                        'part' => 'Part',
                                    ];
                                @endphp

                                @foreach ($paymentMethods as $value => $label)
                                    <label
                                        class="inline-flex items-center space-x-2 text-sm font-medium text-gray-700 cursor-pointer">
                                        <input type="radio" name="payment_method" value="{{ $value }}"
                                            wire:model="payment_method" class="peer hidden" />
                                        <div
                                            class="w-4 h-4 rounded-full border-2 border-gray-400 peer-checked:border-red-500 peer-checked:bg-red-500 transition duration-200">
                                        </div>
                                        <span class="select-none">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>



                            <div class="p-2 md:p-4 border-t mt-2">
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-1 md:gap-2">
                                    <button
                                        class="bg-red-500 text-white px-2 py-1 md:px-4 md:py-2 rounded hover:bg-red-600 text-xs md:text-sm">
                                        Save
                                    </button>
                                    <button
                                        class="bg-red-500 text-white px-2 py-1 md:px-4 md:py-2 rounded hover:bg-red-600 text-xs md:text-sm">
                                        Save & Print
                                    </button>
                                    <button
                                        class="bg-red-500 text-white px-2 py-1 md:px-4 md:py-2 rounded hover:bg-red-600 text-xs md:text-sm">
                                        Save & eBill
                                    </button>
                                    @if ($editMode)
                                        <button
                                            class="bg-gray-600 text-white px-2 py-1 md:px-4 md:py-2 rounded hover:bg-gray-700 text-xs md:text-sm"
                                            wire:click="updateOrder">
                                            Update KOT
                                        </button>
                                    @else
                                        <button wire:click="placeOrder"
                                            class="bg-gray-600 text-white px-2 py-1 md:px-4 md:py-2 rounded hover:bg-gray-700 text-xs md:text-sm">
                                            Kot Order
                                        </button>
                                    @endif
                                    <button
                                        class="bg-gray-600 text-white px-2 py-1 md:px-4 md:py-2 rounded hover:bg-gray-700 text-xs md:text-sm"
                                        wire:click="placeOrderAndPrint">
                                        KOT & Print
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif
                @else
                    <div class="flex-1 flex flex-col items-center justify-center text-gray-500 py-8">
                        <p>Cart empty</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modals (unchanged from original) -->
    @if ($showVariantModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
            <div class="bg-white w-full max-w-sm rounded shadow-lg p-6 mx-2">
                <h3 class="text-lg font-bold mb-4">Select Variant <span class="text-sm font-normal">(optional)</span>
                </h3>

                <div class="space-y-2 mb-6">
                    @foreach ($variantOptions as $opt)
                        <label class="flex items-center gap-2">
                            <input type="radio" wire:model="selectedVariantId" value="{{ $opt['id'] }}">
                            <span>
                                {{ $opt['variant_name'] }} — ₹{{ number_format($opt['combined_price'], 2) }}
                            </span>
                        </label>
                    @endforeach
                </div>

                <div class="flex justify-end gap-2">
                    <button class="px-4 py-2 bg-gray-200 rounded"
                        wire:click="$set('showVariantModal', false)">Cancel</button>

                    <button class="px-4 py-2 bg-indigo-600 text-white rounded" wire:click="addSelectedVariant">
                        Add to Cart
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($showNoteModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white p-4 md:p-6 rounded shadow w-full max-w-md mx-2">
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
            <div class="bg-white p-4 md:p-6 rounded shadow-lg max-w-md w-full mx-2">
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
        <div class="mt-4 md:mt-6 p-2 md:p-4">
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
    <script>
        // Add this to handle the initial state and clicks
        document.addEventListener('DOMContentLoaded', function() {
            // For mobile, we want to show the first 5 categories by default
            const categoryNav = document.querySelector('.category-nav');
            const mobileCategories = document.querySelector('.mobile-categories');

            // On mobile, hide the nav initially (will be toggled by button)
            if (window.innerWidth < 768) {
                categoryNav.classList.add('hidden');
            }

            // Handle window resize
            window.addEventListener('resize', function() {
                if (window.innerWidth >= 768) {
                    // Desktop - show all categories
                    categoryNav.classList.remove('hidden');
                    mobileCategories.classList.remove('hidden');
                } else {
                    // Mobile - hide extra categories
                    mobileCategories.classList.add('hidden');
                }
            });
        });
    </script>
@endpush
