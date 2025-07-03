
    
<div class="bg-gray-100 font-sans">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="flex items-center justify-between px-4 py-3">
            <div class="flex items-center space-x-4">
                <button class="text-gray-600 hover:text-gray-800">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                <div class="flex items-center space-x-2">
                    <img src="https://via.placeholder.com/40x40/FF6B6B/FFFFFF?text=P" alt="PetPooja" class="w-10 h-10 rounded">
                    <span class="text-xl font-semibold text-gray-800">PetPooja</span>
                </div>
                <button class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">
                    New Order
                </button>
                <div class="flex items-center space-x-2">
                    <i class="fas fa-search text-gray-400"></i>
                    <input type="text" placeholder="Bill No" class="border-none outline-none text-gray-600 bg-transparent">
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


            <!-- Order Summary -->
            <div class="w-96 bg-white shadow-lg">
                <div class="flex space-x-2">
                        <button class="bg-red-500 text-white px-6 py-2 rounded hover:bg-red-600">Dine In</button>
                        <button class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">Delivery</button>
                        <button class="bg-gray-300 text-gray-700 px-6 py-2 rounded hover:bg-gray-400">Pick Up</button>
                    </div>

                <div class="p-4">
                    <div class="flex justify-between items-center mb-4">
                        <span class="font-medium">ITEMS</span>
                        <span class="text-sm text-gray-500">CHECK ITEMS</span>
                        <span class="text-sm text-gray-500">QTY.</span>
                        <span class="text-sm text-gray-500">PRICE</span>
                    </div>

                    <div class="space-y-3">
                        <div class="border-b pb-2">
                            <div class="text-sm text-gray-500 mb-1">KOT - 17 Time - 03:56</div>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <button class="text-red-500 hover:text-red-700"><i class="fas fa-times-circle"></i></button>
                                    <span class="text-sm">RasMalai</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button class="text-gray-400 hover:text-gray-600"><i class="fas fa-minus"></i></button>
                                    <span class="text-sm">1</span>
                                    <button class="text-gray-400 hover:text-gray-600"><i class="fas fa-plus"></i></button>
                                </div>
                                <div class="text-sm">
                                    <div>25.00</div>
                                    <div class="text-gray-400">25.00</div>
                                </div>
                            </div>
                        </div>

                        <div class="border-b pb-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <button class="text-red-500 hover:text-red-700"><i class="fas fa-times-circle"></i></button>
                                    <span class="text-sm">Strawberry Mojito</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button class="text-gray-400 hover:text-gray-600"><i class="fas fa-minus"></i></button>
                                    <span class="text-sm">1</span>
                                    <button class="text-gray-400 hover:text-gray-600"><i class="fas fa-plus"></i></button>
                                </div>
                                <div class="text-sm">
                                    <div>75.00</div>
                                    <div class="text-gray-400">75.00</div>
                                </div>
                            </div>
                        </div>

                        <div class="border-b pb-2">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <button class="text-red-500 hover:text-red-700"><i class="fas fa-times-circle"></i></button>
                                    <span class="text-sm">Veg Burger</span>
                                </div>
                                <div class="flex items-center space-x-2">
                                    <button class="text-gray-400 hover:text-gray-600"><i class="fas fa-minus"></i></button>
                                    <span class="text-sm">1</span>
                                    <button class="text-gray-400 hover:text-gray-600"><i class="fas fa-plus"></i></button>
                                </div>
                                <div class="text-sm">
                                    <div>80.00</div>
                                    <div class="text-gray-400">80.00</div>
                                </div>
                            </div>
                        </div>
                    </div>

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
                            <div class="text-xl font-bold">Total 180</div>
                        </div>
                    </div>
                </div>

                <div class="p-4 border-t">
                    <div class="flex space-x-2">
                        <button class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Save</button>
                        <button class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Save & Print</button>
                        <button class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Save & eBill</button>
                        <button class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">KOT</button>
                        <button class="bg-gray-600 text-white px-4 py-2 rounded hover:bg-gray-700">KOT & Print</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add basic interactivity
        document.addEventListener('DOMContentLoaded', function() {
            // Menu item clicks
            const menuItems = document.querySelectorAll('.grid > div');
            menuItems.forEach(item => {
                item.addEventListener('click', function() {
                    this.classList.add('ring-2', 'ring-blue-500');
                    setTimeout(() => {
                        this.classList.remove('ring-2', 'ring-blue-500');
                    }, 300);
                });
            });

            // Quantity buttons
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

            // Order type buttons
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
</div>
