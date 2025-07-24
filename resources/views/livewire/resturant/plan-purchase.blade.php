<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 p-6">
    @foreach ($plans as $plan)
        <div x-data="{ show: false }"
            x-init="setTimeout(() => show = true, 100 * {{ $loop->index }})"
            x-show="show"
            x-transition.duration.500ms
            class="bg-white shadow-xl rounded-2xl border border-gray-200 hover:shadow-2xl transition-transform transform hover:-translate-y-1 cursor-pointer">
            <div class="p-6 space-y-3">
                <h2 class="text-2xl font-bold text-indigo-600">{{ $plan->name }}</h2>
                <p class="text-gray-500 text-sm">{{ $plan->description }}</p>

                <div class="flex items-center justify-between mt-4">
                    <span class="text-lg font-semibold text-gray-800">₹{{ number_format($plan->price, 2) }}</span>
                    <span class="text-sm text-gray-500">{{ $plan->duration_days }} Days</span>
                </div>

                <div class="mt-6">
                    <button onclick="startPlanPurchase({{ $plan->id }}, {{ (int) $plan->price * 100 }})"
                        class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition duration-300">
                        {{ $plan->price == 0 ? 'Start Free Trial' : 'Buy Now' }}
                    </button>
                </div>
            </div>
        </div>
    @endforeach
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
            .then(({ success, redirect }) => {
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
                        theme: { color: "#738276" }
                    };
                    new Razorpay(options).open();
                })
                .catch(() => alert("Payment initiation failed."));
        }
    }
</script>
@endpush
