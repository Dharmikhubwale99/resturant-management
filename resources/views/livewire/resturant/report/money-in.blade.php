<div class="p-6 max-w-7xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold mb-4">Money In Report</h1>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 flex-wrap">
        <div class="flex flex-col sm:flex-row gap-2 flex-wrap">
            <select wire:model.live="dateFilter" class="border border-gray-300 p-2 rounded w-full sm:w-auto">
                <option value="today">Today</option>
                <option value="weekly">Weekly</option>
                <option value="monthly">Monthly</option>
                <option value="custom">Custom</option>
            </select>

            @if ($dateFilter === 'custom')
                <input type="date" wire:model.live="fromDate"
                    class="border border-gray-300 p-2 rounded w-full sm:w-auto">
                <input type="date" wire:model.live="toDate"
                    class="border border-gray-300 p-2 rounded w-full sm:w-auto">
            @endif


            <select wire:model.live="methodFilter" class="border border-gray-300 p-2 rounded w-full sm:w-auto">
                <option value="">All Methods</option>
                <option value="cash">Cash</option>
                <option value="upi">UPI</option>
                <option value="card">Card</option>
                <option value="part">Part</option>
                <option value="duo">Due</option>
            </select>
        </div>


        <div class="flex flex-wrap gap-2">
            <x-form.button type="button" title="Export to Excel" wireClick="exportExcel" wireTarget="exportExcel"
                class="bg-green-500 text-white px-4 py-2 rounded" />
            <x-form.button type="button" title="Export to PDF" wireClick="exportPdf" wireTarget="exportPdf"
                class="bg-blue-500 text-white px-4 py-2 rounded" />
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border border-gray-300 mb-6">
            <thead class="bg-orange-400 text-black text-center">
                <tr>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Sr No</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Date</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Order ID</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Party Name</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Party Phone</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Status</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Total</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Payment Type</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Payment</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($orders as $index => $order)
                    <tr class="hover:bg-gray-50 text-sm text-center">
                        <td class="px-4 py-2 whitespace-nowrap">{{ $index + 1 }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $order->created_at->format('d-m-Y') }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $order->order_number }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            {{ $order->customer_name ?? ($order->table->name ?? 'N/A') }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $order->mobile ?? '-' }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ ucfirst($order->status) }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">₹{{ $order->total_amount }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $order->payment->method ?? '' }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            @php
                                $logs = $order->paymentLogs;
                                $paid = $logs->sum('amount');
                                $method = optional($order->payment)->method;
                            @endphp

                            @if ($method === 'part' && $order->paymentGroups->count())
                                <span class="text-green-600">
                                    {{ $order->paymentGroups->groupBy('method')->map(fn($group, $m) => ucfirst($m) . ': ₹' . $group->sum('amount'))->implode(' | ') }}
                                </span>
                            @elseif ($logs->isNotEmpty())
                                @foreach ($logs->groupBy('method') as $logMethod => $group)
                                    <div>
                                        <span class="text-green-600">{{ ucfirst($logMethod) }}:
                                            ₹{{ $group->sum('paid_amount') }}</span> |
                                        <span class="text-red-400">₹{{ $group->sum('amount') }}</span>
                                    </div>
                                @endforeach
                            @else
                                <span class="text-green-600">{{ ucfirst($method) ?? '-' }} :
                                    ₹{{ $order->total_amount }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-3 text-center text-sm text-gray-500">
                            No sales records found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</div>
