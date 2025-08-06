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

                        <a href="{{ route('restaurant.dashboard') }}" class="text-gray-600 hover:text-gray-800">
                            <i class="fas fa-arrow-left text-xl"></i>
                        </a>
                        <img src="{{ asset('assets/images/logo.jpeg') }}" alt="HubWale"
                            class="w-8 h-8 md:w-10 md:h-10 rounded">
                    </div>
                </div>
                <div class="flex items-center space-x-2 md:hidden">
                    <button wire:click="openCustomerModal" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-user text-lg"></i>
                    </button>

                    <button class="text-gray-600 hover:text-gray-800"><i class="fas fa-power-off text-lg"></i></button>
                </div>
            </div>

            <!-- Bottom Row (Desktop) -->
            <div class="hidden md:flex items-center space-x-4">

                <div class="flex items-center space-x-3">
                    <!-- Pending KOT  (PDF icon) -->
                    <a href="{{ route('restaurant.kots.pending') }}" class="text-gray-600 hover:text-gray-800"
                        title="Pending KOT Orders">
                        <i class="fas fa-file-pdf text-lg"></i>
                    </a>
                    <button class="text-gray-600 hover:text-gray-800"><i class="fas fa-calculator text-lg"></i></button>
                    <button class="text-gray-600 hover:text-gray-800"><i class="fas fa-th text-lg"></i></button>
                    <button class="text-gray-600 hover:text-gray-800"><i class="fas fa-clock text-lg"></i></button>
                    <button class="text-gray-600 hover:text-gray-800"><i class="fas fa-bell text-lg"></i></button>
                    <button wire:click="openCustomerModal" class="text-gray-600 hover:text-gray-800">
                        <i class="fas fa-user text-lg"></i>
                    </button>

                    <button class="text-gray-600 hover:text-gray-800"><i class="fas fa-power-off text-lg"></i></button>
                </div>
            </div>
        </div>
    </header>

    <div class="flex flex-col md:flex-row h-full">
        <!-- Sidebar - Responsive -->
        @if (setting('category_module'))
            <div class="w-full md:w-64 h-full md:min-h-screen bg-gray-800 text-white flex-shrink-0 flex flex-col">
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
                    <div class="flex flex-wrap gap-2">
                        <input type="text" wire:model.live="search"
                            placeholder="Search by product, code, or short name..."
                            class="border px-3 py-2 rounded w-50" />
                    </div>
                </div>

                <x-form.error />

                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-3 lg:grid-cols-5 gap-2 md:gap-4">
                    @forelse ($filteredItems as $item)
                        <div wire:click="itemClicked({{ $item->id }})"
                            class="relative bg-white p-1 md:p-2 rounded shadow hover:shadow-md transition
                        border-2 {{ $item->type_color_class }} cursor-pointer">
                            @php
                                $cartQty = 0;

                                if (is_array($cart)) {
                                    foreach ($cart as $key => $cartRow) {
                                        if (
                                            is_array($cartRow) &&
                                            isset($cartRow['item_id']) &&
                                            $cartRow['item_id'] == $item->id
                                        ) {
                                            $cartQty += $cartRow['qty'] ?? 0;
                                        } elseif ((string) $key === (string) $item->id && isset($cartRow['qty'])) {
                                            $cartQty += $cartRow['qty'];
                                        }
                                    }
                                }
                            @endphp
                            @if ($cartQty > 0)
                                <span
                                    class="absolute top-1 left-1 bg-red-500 text-white text-xs font-bold rounded-full px-2 py-0.5 z-10">
                                    {{ $cartQty }}
                                </span>
                            @endif
                            <span
                                class="absolute top-1 right-1 w-2 h-2 md:w-3 md:h-3 rounded-full
                     {{ $item->type_dot_class }}"></span>

                            <img src="{{ $item->getFirstMediaUrl('images') ?: asset('icon/hubwalelogopng.png') }}"
                                class="w-full h-20 md:h-28 object-cover rounded mb-1 md:mb-2"
                                alt="{{ $item->name }}">
                            <h3 class="text-xs md:text-sm font-semibold text-center truncate px-1">{{ $item->name }}
                            </h3>
                            @php
                                $discount = $item->discounts->where('is_active', 0)->first();
                                $hasDiscount = $discount !== null;
                                $finalPrice = $item->price;
                                $discountLable = '';

                                if ($hasDiscount) {
                                    if ($discount->type === 'percentage' && $discount->value > 0) {
                                        $finalPrice -= ($item->price * $discount->value) / 100;
                                        $discountLable = $discount->value . '%';
                                    } elseif ($discount->type === 'fixed' && $discount->minimum_amount > 0) {
                                        $finalPrice -= $discount->minimum_amount;
                                        $discountLable = '₹' . number_format($discount->minimum_amount, 2);
                                    }

                                    $finalPrice = max(0, $finalPrice);
                                }
                            @endphp

                            @if ($hasDiscount)
                                <p class="text-gray-500 text-xs md:text-sm line-through">
                                    ₹{{ number_format($item->price, 2) }}
                                </p>
                                <p class="text-blue-700 font-bold text-xs md:text-sm">
                                    ₹{{ number_format($finalPrice, 2) }} ({{ $discountLable }} off)
                                </p>
                            @else
                                <p class="text-blue-700 font-bold text-xs md:text-sm">
                                    ₹{{ number_format($item->price, 2) }}
                                </p>
                            @endif

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
                <h2 class="text-md md:text-lg font-bold mb-2 md:mb-4 text-center">Cart</h2>
                @if (count($cartItems))
                    <div class="flex-1 overflow-y-auto space-y-2 md:space-y-3">
                        @if ($editMode)
                            <p class="text-xs md:text-sm font-semibold text-blue-600">Previous KOT Items:</p>
                        @endif

                        @foreach ($cartItems as $key => $row)
                            @if (in_array($key, $originalKotItemKeys) && $row['qty'] > 0)
                                <div class="border rounded p-1 md:p-2 flex items-center justify-between bg-gray-50"
                                    wire:key="row-{{ $row['id'] }}">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-xs text-blue-600 font-semibold mb-0.5">
                                            KOT: #{{ $row['kot_number'] ?? '-' }} • {{ $row['kot_time'] ?? '-' }}
                                        </p>
                                        <p class="font-semibold flex items-center gap-1 text-xs md:text-sm truncate">
                                            {{ $row['name'] }}
                                        </p>
                                        <div class="flex flex-row items-baseline gap-2">
                                            <p class="text-xs text-gray-500 whitespace-nowrap">
                                                ₹{{ number_format($row['price'], 2) }} × {{ $row['qty'] }}
                                            </p>
                                            <p class="text-xs text-green-600 font-semibold whitespace-nowrap">
                                                = ₹{{ number_format($row['price'] * $row['qty'], 2) }}
                                            </p>
                                            <button class="text-red-500 text-xs md:text-sm"
                                                wire:click="remove('{{ $row['id'] }}')">✕</button>
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
                                            <button
                                                class="text-xs bg-yellow-100 text-yellow-700 px-1 md:px-2 rounded mt-1"
                                                wire:click="openPriceModal('{{ $row['id'] }}')">Edit Price</button>

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
                            <button wire:click="$set('showCartDetailModal', true)"
                                class="bg-blue-500 text-white px-2 py-1 rounded text-xs md:text-sm">
                                Cart Details
                            </button>

                            {{-- <div class="flex flex-wrap items-center gap-1 md:gap-2 mb-1 md:mb-2">
                                <button class="bg-red-500 text-white px-2 py-1 rounded text-xs md:text-sm flex-1">Bogo
                                    Offer</button>
                                <button
                                    class="bg-gray-300 text-gray-700 px-2 py-1 rounded text-xs md:text-sm flex-1">Split</button>
                            </div> --}}

                            <div class="text-right text-lg md:text-xl font-bold py-1 md:py-2">
                                Total: ₹{{ number_format($cartTotal, 2) }}
                            </div>

                            <div class="flex flex-wrap justify-center gap-4 mt-3 mb-3">
                                @foreach ($paymentMethods as $value => $label)
                                    <label
                                        class="inline-flex items-center space-x-2 text-sm font-medium text-gray-700 cursor-pointer">
                                        <input type="radio" name="payment_method" value="{{ $value }}"
                                            wire:model="paymentMethod" class="peer hidden" />
                                        <div
                                            class="w-4 h-4 rounded-full border-2 border-gray-400 peer-checked:border-red-500 peer-checked:bg-red-500">
                                        </div>
                                        <span>{{ $label }}</span>
                                    </label>
                                @endforeach

                                @error('paymentMethod')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="p-2 md:p-4 border-t mt-2">
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-1 md:gap-2">
                                    <button wire:click="save"
                                        class="bg-red-500 text-white px-2 py-1 md:px-4 md:py-2 rounded hover:bg-red-600 text-xs md:text-sm">
                                        Save
                                    </button>
                                    <button wire:click="saveAndPrint"
                                        class="bg-red-500 text-white px-2 py-1 md:px-4 md:py-2 rounded hover:bg-red-600 text-xs md:text-sm">
                                        Save & Print
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

    @if ($showVariantModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-transparent bg-opacity-40 backdrop-blur-lg">
            <div class="bg-white w-full max-w-sm rounded shadow-lg p-6 mx-2">
                @if ($variantOptions)
                    <h3 class="text-lg font-bold mb-4">Select Variant <span
                            class="text-sm font-normal">(optional)</span>
                    </h3>

                    <div class="space-y-2 mb-6">
                        @foreach ($variantOptions as $opt)
                            <label class="flex items-center gap-2">
                                <input type="radio" wire:model="selectedVariantId" value="{{ $opt['id'] }}">
                                <span>
                                    {{ $opt['variant_name'] }} — ₹{{ number_format($opt['variant_price'], 2) }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                @endif

                @if (count($addonOptions))
                    <div class="mb-4">
                        <h4 class="text-sm font-semibold mb-2">Select Addons</h4>
                        @foreach ($addonOptions as $addon)
                            <label class="flex items-center space-x-2 mb-1">
                                <input type="checkbox" wire:model="selectedAddons" value="{{ $addon['id'] }}">
                                <span>{{ $addon['name'] }} — ₹{{ number_format($addon['price'], 2) }}</span>
                            </label>
                        @endforeach
                    </div>
                @endif
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
        <div class="fixed inset-0 bg-transparent bg-opacity-40 backdrop-blur-lg flex items-center justify-center z-50">
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

    @if ($showSplitModal)
        <div class="fixed inset-0 bg-transparent bg-opacity-40 backdrop-blur-lg flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-4 w-full max-w-md">
                <h3 class="font-bold mb-3">Split Payment</h3>
                <x-form.error />
                <div class="mb-2">
                    <label class="block text-sm font-semibold mb-1">Customer Name</label>
                    <input type="text" wire:model="customerName" class="border rounded p-2 w-full"
                        placeholder="Enter customer name">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-1">Mobile Number</label>
                    <input type="text" wire:model="mobile" maxlength="20" class="border rounded p-2 w-full"
                        placeholder="9876543210">
                </div>

                @foreach ($splits as $index => $row)
                    <div class="flex gap-2 mb-2 items-center">
                        <select wire:model="splits.{{ $index }}.method" class="border rounded p-1 flex-1">
                            <option value="">-- Method --</option>
                            @foreach ($paymentMethods as $val => $lbl)
                                @if ($val !== 'part')
                                    <option value="{{ $val }}">{{ $lbl }}</option>
                                @endif
                            @endforeach
                        </select>

                        <input type="number" min="1" step="0.01"
                            wire:model="splits.{{ $index }}.amount" class="border rounded p-1 w-24"
                            placeholder="Amt" />

                        <button wire:click="removeSplit({{ $index }})"
                            class="text-red-600 text-lg">&times;</button>
                    </div>
                @endforeach

                <button wire:click="addSplit" class="bg-gray-200 px-3 py-1 text-sm rounded mb-4">+ Add</button>

                <div class="flex justify-end gap-2">
                    <button wire:click="$set('showSplitModal', false)"
                        class="bg-gray-300 px-3 py-1 rounded">Cancel</button>
                    <button wire:click="confirmSplit" class="bg-red-500 text-white px-3 py-1 rounded">Save</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showDuoPaymentModal)
        <div class="fixed inset-0 z-50 bg-transparent bg-opacity-40 backdrop-blur-lg flex items-center justify-center">
            <div class="bg-white rounded shadow p-4 w-full max-w-md">
                <h3 class="text-lg font-bold mb-4">Duo Payment Details</h3>
                <x-form.error />

                <div class="mb-2">
                    <label class="block text-sm font-semibold">Customer Name</label>
                    <input type="text" wire:model.defer="duoCustomerName" class="border rounded w-full p-2" />
                </div>

                <div class="mb-2">
                    <label class="block text-sm font-semibold">Mobile Number</label>
                    <input type="text" wire:model.defer="duoMobile" maxlength="20"
                        class="border rounded w-full p-2" />
                </div>

                <div class="mb-2">
                    <label class="block text-sm font-semibold">Payment Method</label>
                    <select wire:model="duoMethod" class="border rounded w-full p-2">
                        <option value=""> Select Method </option>
                        @foreach ($paymentMethods as $val => $lbl)
                            @if ($val !== 'duo' && $val !== 'part')
                                <option value="{{ $val }}">{{ $lbl }}</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                <div class="mb-2">
                    <label class="block text-sm font-semibold">Amount</label>
                    <input type="number" min="0" step="0.01" wire:model.live="duoAmount"
                        class="border rounded w-full p-2" />
                    <span class="text-sm text-gray-600 mt-1 block">
                        Amount: ₹{{ number_format($cartTotal - floatval($duoAmount), 2) }}
                    </span>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold">Issue</label>
                    <textarea wire:model="duoIssue" class="border rounded w-full p-2" rows="3"></textarea>
                </div>

                <div class="flex justify-end gap-2">
                    <button wire:click="$set('showDuoPaymentModal', false)"
                        class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
                    <button wire:click="confirmDuoPayment"
                        class="bg-red-500 text-white px-4 py-2 rounded">Confirm</button>
                </div>
            </div>
        </div>
    @endif

    @if ($showPriceModal)
        <div
            class="fixed inset-0 bg-bg-transparent bg-opacity-40 backdrop-blur-lg flex items-center justify-center z-50">
            <div class="bg-white p-4 md:p-6 rounded shadow w-full max-w-md mx-2">
                <h2 class="text-lg font-bold mb-4">Edit Item Price</h2>

                <p class="text-sm font-medium mb-1">
                    <strong>Item:</strong> {{ $priceItemName }}<br>
                    <strong>Original Price:</strong> ₹{{ number_format($originalPrice, 2) }}
                </p>

                <div class="mb-2">
                    <label class="block text-sm font-semibold mb-1">Discount Type</label>
                    <select wire:model.live="discountType" class="w-full border rounded p-2">
                        <option value="percentage">Percentage (%)</option>
                        <option value="fixed">Fixed (₹)</option>
                    </select>
                </div>

                <div class="mb-2">
                    <label class="block text-sm font-semibold mb-1">Discount Value</label>
                    <input type="number" min="0" wire:model.live="discountValue"
                        class="w-full border rounded p-2" placeholder="e.g. 10 for 10% " />
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-semibold mb-1">Final Price</label>
                    <input type="number" step="0.01" min="0" wire:model.defer="priceInput"
                        class="w-full border rounded p-2" readonly />
                </div>

                <div class="flex justify-end gap-2">
                    <button wire:click="$set('showPriceModal', false)" class="px-4 py-2 bg-gray-200 rounded">
                        Cancel
                    </button>
                    <button wire:click="savePrice" class="px-4 py-2 bg-green-600 text-white rounded">
                        Save Price
                    </button>
                </div>
            </div>
        </div>
    @endif



    @if ($showCartDetailModal)
        <div class="fixed inset-0 bg-bg-transparent bg-opacity-40 backdrop-blur-lg flex items-center justify-center">
            <div class="bg-white rounded shadow-lg p-6 w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                <h2 class="text-lg font-bold mb-4">Cart Details</h2>

                <!-- Cart Items -->
                <div class="mb-4 space-y-2">
                    <h3 class="text-md font-semibold text-gray-700 mb-2">Items in Cart</h3>
                    @forelse ($cart as $key => $item)
                        @if ($item['qty'] > 0)
                            <div class="border p-2 rounded text-sm bg-gray-50">
                                <div class="font-semibold">{{ $item['name'] }}</div>
                                <div class="text-gray-600">
                                    Qty: {{ $item['qty'] }} × ₹{{ number_format($item['price'], 2) }}
                                </div>
                                <div class="flex flex-row justify-between text-blue-600 font-semibold">
                                    Total: ₹{{ number_format($item['qty'] * $item['price'], 2) }}
                                    <button class="text-xs bg-yellow-100 text-yellow-700 px-1 md:px-2 rounded mt-1"
                                        wire:click="openPriceModal('{{ $row['id'] }}')">Edit Price</button>
                                    <div class="flex justify-end items-center">
                                        <button class="text-red-500 text-xs md:text-sm"
                                            wire:click="remove('{{ $row['id'] }}')">✕</button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @empty
                        <p class="text-gray-500">Cart is empty.</p>
                    @endforelse
                </div>

                <!-- Charges -->
                <div class="mb-3">
                    <label class="block text-sm font-semibold">Service Charge (₹)</label>
                    <input type="number" step="0.01" wire:model.live="serviceCharge"
                        class="w-full border rounded p-2" />
                </div>
                <div class="flex flex-row justify-between gap-2">
                    <div class="mb-3">
                        <label class="block text-sm font-semibold">Discount Type</label>
                        <select wire:model.live="cartDiscountType" class="w-full border rounded p-2">
                            <option value="percentage">Percentage (%)</option>
                            <option value="fixed">Fixed (₹)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="block text-sm font-semibold">Discount Value</label>
                        <input type="number" min="0" step="0.01" wire:model.live="cartDiscountValue"
                            class="w-full border rounded p-2" placeholder="e.g. 10 or 100" />
                    </div>
                </div>

                <!-- Totals -->
                <div class="mb-4 text-sm text-gray-700">
                    <p>Subtotal: ₹{{ number_format($this->getSubtotal(), 2) }}</p>
                    <p>Service Charge: ₹{{ number_format($serviceCharge, 2) }}</p>
                    <p>
                        Discount:
                        @if ($cartDiscountType === 'percentage')
                            {{ $cartDiscountValue }}%
                        @else
                            ₹{{ number_format($cartDiscountValue, 2) }}
                        @endif
                    </p>
                    <p class="font-bold text-blue-600">Total: ₹{{ number_format($this->getCartTotal(), 2) }}</p>
                </div>


                <div class="flex justify-end gap-2">
                    <button wire:click="$set('showCartDetailModal', false)" class="px-4 py-2 bg-gray-200 rounded">
                        Close
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($showRemoveModal)
        <div
            class="fixed inset-0 bg-bg-transparent bg-opacity-40 backdrop-blur-lg flex items-center justify-center z-50">
            <div class="bg-white rounded shadow p-4 w-full max-w-md mx-2">
                <h2 class="text-lg font-bold mb-3">Remove Item</h2>
                <p class="mb-2 text-sm text-gray-700">Please enter the reason for removing this item:</p>
                <textarea wire:model.defer="removeReason" rows="3" class="w-full border rounded p-2"></textarea>

                <div class="flex justify-end gap-2 mt-4">
                    <button wire:click="$set('showRemoveModal', false)" class="px-4 py-2 bg-gray-200 rounded">
                        Cancel
                    </button>
                    <button wire:click="confirmRemove" class="px-4 py-2 bg-red-600 text-white rounded">
                        Confirm Remove
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($showCustomerModal)
        <div
            class="fixed inset-0 bg-bg-transparent bg-opacity-40 backdrop-blur-lg flex justify-center items-center z-50">
            <div class="bg-white p-6 rounded shadow w-full max-w-md">
                <h2 class="text-lg font-bold mb-4">Customer Details</h2>

                <x-form.error />

                <div class="space-y-3">
                    <input type="text" wire:model.defer="followupCustomer_name" class="w-full border p-2 rounded"
                        placeholder="Customer Name">

                    <input type="text" wire:model.defer="followupCustomer_mobile"
                        class="w-full border p-2 rounded" placeholder="Mobile Number" maxlength="10"
                        oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)">

                    <input type="email" wire:model.defer="followupCustomer_email" class="w-full border p-2 rounded"
                        placeholder="Email (optional)">

                    <input type="date" wire:model.defer="customer_dob" class="w-full border p-2 rounded"
                        placeholder="DOB (optional)">

                    <input type="date" wire:model.defer="customer_anniversary" class="w-full border p-2 rounded"
                        placeholder="Anniversary (optional)">
                </div>

                <div class="mt-4 flex justify-end gap-2">
                    <button wire:click="$set('showCustomerModal', false)"
                        class="bg-gray-300 px-4 py-2 rounded">Cancel</button>
                    <button wire:click="saveCustomer" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
                </div>
            </div>
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
            window.open(`/restaurant/kot-print/${kotId}`, '_blank');
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


    <script>
        Livewire.on('printBill', (event) => {
            const billId = event.billId;
            window.open(`/restaurant/bill-print/${billId}`, '_blank');
        });
    </script>
@endpush
