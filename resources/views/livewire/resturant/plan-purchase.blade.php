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
                        <div class="px-4 py-3 ">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>


    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
        @foreach ($plans as $plan)
            @php
                $originalPrice = $plan->price;
                $finalPrice = $originalPrice;

                if ($plan->type === 'fixed' && $plan->amount) {
                    $finalPrice -= $plan->amount;
                } elseif ($plan->type === 'percentage' && $plan->value) {
                    $finalPrice -= ($originalPrice * $plan->value) / 100;
                }

                $finalPrice = max(0, $finalPrice);
                $youSave = $originalPrice - $finalPrice;
            @endphp

            <div x-data="{ show: false }" x-init="setTimeout(() => show = true, 100 * {{ $loop->index }})" x-show="show" x-transition.duration.500ms
                class="bg-white shadow-xl rounded-2xl border border-gray-200 hover:shadow-2xl transition-transform transform hover:-translate-y-1 cursor-pointer">

                <div class="p-6 space-y-3">
                    <h2 class="text-2xl font-bold text-indigo-600">{{ $plan->name }}</h2>
                    <p class="text-gray-500 text-sm">{{ $plan->description }}</p>

                    <div class="flex items-center justify-between mt-4">
                        <div>
                            @if ($finalPrice < $originalPrice)
                                <div>
                                    <span
                                        class="text-sm line-through text-red-400">₹{{ number_format($originalPrice, 2) }}</span>
                                    <span
                                        class="text-lg font-bold text-green-600 ml-2">₹{{ number_format($finalPrice, 2) }}</span>
                                </div>
                            @else
                                <span
                                    class="text-lg font-bold text-gray-800">₹{{ number_format($finalPrice, 2) }}</span>
                            @endif
                        </div>
                        <span class="text-sm text-gray-500">{{ $plan->duration_days }} Days</span>
                    </div>

                    <div class="mt-6">
                        <button onclick="startPlanPurchase({{ $plan->id }}, {{ (int) ($finalPrice * 100) }})"
                            class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-300">
                            {{ $finalPrice == 0 ? 'Start Free Trial' : 'Buy Now' }}
                        </button>
                    </div>
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
