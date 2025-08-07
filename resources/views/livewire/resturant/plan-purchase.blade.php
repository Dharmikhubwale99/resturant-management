<div>
    <nav class="bg-white shadow-lg sticky top-0 z-50" x-data="{ mobileMenuOpen: false, profileMenuOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-end h-16 items-center">
                <div class="relative">
                    <button @click="profileMenuOpen = !profileMenuOpen"
                        class="flex items-center space-x-2 focus:outline-none">
                        <img src="{{ asset('image/Admin.png') }}" alt="Company Profile"
                            class="w-10 h-10 rounded-full object-cover border-2 border-gray-300 hover:border-blue-500 transition-colors duration-200" />
                    </button>

                    <div x-show="profileMenuOpen" @click.away="profileMenuOpen = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                        <div class="px-4 py-3 border-b">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                        </div>
                        <div class="py-1">
                            <a href="{{ route('logout') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 p-6">
        @foreach ($plans as $plan)
            @php
                $originalPrice = (float) $plan->price;
                $finalPrice = $originalPrice;
                $discount = 0;
                $discountLabel = '';

                if ($plan->type === 'fixed' && $plan->amount) {
                    $discount = (float) $plan->amount;
                    $finalPrice -= $discount;
                    $discountLabel = '₹' . number_format($discount, 2);
                } elseif ($plan->type === 'percentage' && $plan->value) {
                    $discount = ($originalPrice * (float) $plan->value) / 100;
                    $finalPrice -= $discount;
                    $discountLabel = $plan->value . '%';
                }

                $finalPrice = max(0, $finalPrice);
                $youSave = $originalPrice - $finalPrice;

                $features = $planFeatures[$plan->id] ?? collect();
            @endphp

            <div x-data="{ show: false }" x-init="setTimeout(() => show = true, 100 * {{ $loop->index }})" x-show="show" x-transition.duration.500ms
                class="bg-white shadow-xl rounded-2xl border border-gray-200 hover:shadow-2xl transition-transform transform hover:-translate-y-1 cursor-pointer p-6 space-y-4 w-full">

                <h2 class="text-2xl text-center font-bold text-indigo-600">{{ $plan->name }}</h2>
                <p class="text-gray-500 text-sm">{{ $plan->description }}</p>

                <div class="flex items-center justify-between mt-2">
                    <div>
                        @if ($finalPrice < $originalPrice)
                            <div>
                                <span class="text-3xl font-extrabold line-through text-red-400">
                                    ₹{{ number_format($originalPrice, 2) }}
                                </span>
                                <span class="text-lg font-bold text-green-600 ml-2">
                                    ₹{{ number_format($finalPrice, 2) }}
                                    ({{ $discountLabel }} off)
                                </span>
                            </div>
                        @else
                            <span class="text-lg font-extrabold  ml-2">
                                ₹{{ number_format($finalPrice, 2) }}
                            </span>
                        @endif
                    </div>
                    <span class="text-sm text-gray-500">{{ $plan->duration_days }} Days</span>
                </div>

                <div class="mb-5">
                    <ul class="grid grid-cols-1 gap-2">
                        @foreach ($modules as $module)
                            @php
                                $enabled = $features->contains('feature', $module->key);
                            @endphp
                            <li class="flex items-center gap-4 bg-gray-50 px-3 py-1 rounded-md">

                                @if ($enabled)
                                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M16.707 5.293a1 1 0 00-1.414 0L9 11.586 6.707 9.293a1 1 0 10-1.414 1.414l3 3a1 1 0 001.414 0l7-7a1 1 0 000-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor"
                                        stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                @endif
                                <span
                                    class="text-sm text-gray-800 capitalize">{{ str_replace('_', ' ', $module->key) }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="mt-4">
                    <button onclick="startPlanPurchase({{ $plan->id }}, {{ (int) ($finalPrice * 100) }})"
                        class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-300">
                        {{ $finalPrice == 0 ? 'Start Free Trial' : 'Buy Now' }}
                    </button>
                </div>
            </div>
        @endforeach
    </div>
</div>
@push('scripts')
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        function startPlanPurchase(planId, amountPaisa) {
            if (amountPaisa === 0) {
                fetch(`/activate-free-plan/${planId}`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(r => r.json())
                    .then(({
                        success,
                        redirect
                    }) => {
                        if (success) {
                            window.location.href = redirect;
                        } else {
                            alert("Unable to activate free trial.");
                        }
                    })
                    .catch(() => alert("Something went wrong. Try again."));
            } else {
                fetch(`/create-razorpay-order/${planId}`)
                    .then(response => response.json())
                    .then(data => {
                        const options = {
                            key: data.api_key,
                            amount: data.amount,
                            currency: "INR",
                            name: "Hubwale",
                            description: data.plan_name + " Plan",
                            image: "https://cdn.razorpay.com/logos/GhRQcyean79PqE_medium.png",
                            order_id: data.order_id,
                            callback_url: data.callback_url,
                            theme: {
                                color: "#738276"
                            }
                        };
                        new Razorpay(options).open();
                    })
                    .catch(() => alert("Payment initiation failed."));
            }
        }
    </script>
@endpush
