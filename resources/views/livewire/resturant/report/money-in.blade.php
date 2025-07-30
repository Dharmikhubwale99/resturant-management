<div class="p-4">
    <!-- Filters -->
    <div class="flex flex-wrap gap-4 mb-4">
        <!-- Date Filter -->
        <select wire:model.live="dateFilter" class="border p-2 rounded">
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
            <option value="custom">Custom</option>
        </select>

        @if ($dateFilter === 'custom')
            <input type="date" wire:model.live="fromDate" class="border p-2 rounded">
            <input type="date" wire:model.live="toDate" class="border p-2 rounded">
        @endif

        <!-- Method Filter -->
        <select wire:model="methodFilter" class="border p-2 rounded">
            <option value="">All Methods</option>
            <option value="cash">Cash</option>
            <option value="upi">UPI</option>
            <option value="card">Card</option>
            <option value="part">Part</option>
            <option value="due">Due</option>
        </select>
    </div>

    <!-- Orders Table -->
    <table class="w-full border-collapse border">
        <thead>
            <tr class="bg-gray-100">
                <th class="border p-2">Order ID</th>
                <th class="border p-2">Status</th>
                <th class="border p-2">Total</th>
                <th class="border p-2">Payment</th>
                <th class="border p-2">Details</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $order)
                <tr>
                    <td class="border p-2">{{ $order->order_id }}</td>
                    <td class="border p-2">{{ ucfirst($order->status) }}</td>
                    <td class="border p-2">₹{{ $order->total_amount }}</td>
                    <td class="border p-2">
                        @php
                            $logs = $order->paymentLogs;
                            $paid = $logs->sum('amount');
                        @endphp
                        @if ($logs->isEmpty())
                            <span class="text-red-500">Complete Payment</span>
                        @else
                            @foreach ($logs->groupBy('method') as $method => $group)
                                <div>{{ ucfirst($method) }}: ₹{{ $group->sum('paid_amount') }}</div>
                            @endforeach
                        @endif
                    </td>
                    <td class="border p-2">
                        @if (optional($order->payment)->method === 'duo')
                            <button wire:click="toggleDetails({{ $order->id }})"
                                class="bg-blue-100 text-blue-600 px-2 py-1 rounded">
                                {{ $selectedOrderId === $order->id ? 'Hide' : 'Details' }}
                            </button>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif

                    </td>
                </tr>

                @if ($selectedOrderId === $order->id)
                    <tr>
                        <td colspan="5" class="bg-gray-100 p-4">
                            <h2 class="text-md font-semibold mb-2 text-gray-800">Payment Breakdown</h2>
                            @if ($order->paymentLogs->count())
                                <table class="w-full table-auto border text-sm">
                                    <thead class="bg-gray-200 text-gray-700">
                                        <tr>
                                            <th class="px-3 py-2">Method</th>
                                            <th class="px-3 py-2">Amount</th>
                                            <th class="px-3 py-2">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-center">
                                        @foreach ($order->paymentLogs as $log)
                                            <tr>
                                                <td class="px-3 py-1 capitalize">{{ $log->method }}</td>
                                                <td class="px-3 py-1">₹{{ $log->amount + $log->paid_amount }}</td>
                                                <td class="px-3 py-1">
                                                    {{ \Carbon\Carbon::parse($log->created_at)->format('d-m-Y H:i') }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                <div class="text-right mt-2">
                                    <strong>Total Paid:</strong> ₹{{ $order->paymentLogs->sum('paid_amount') }}<br>
                                    <strong>Due:</strong>
                                    ₹{{ $order->paymentLogs->sum('amount') }}
                                </div>
                            @else
                                <p class="text-red-600">No payment logs available.</p>
                            @endif
                        </td>
                    </tr>
                @endif
            @endforeach
        </tbody>

    </table>

    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</div>
