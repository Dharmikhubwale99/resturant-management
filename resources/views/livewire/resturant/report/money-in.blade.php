<div class="p-6">
    <h1 class="text-2xl font-bold mb-4">Money In Report</h1>

    <div class="flex flex-wrap items-center gap-4 mb-6">
        <div>
            <label class="block text-sm mb-1 font-medium">Date Filter</label>
            <select wire:model.live="dateFilter" class="border px-3 py-1 rounded text-sm">
                <option value="today">Today</option>
                <option value="weekly">This Week</option>
                <option value="monthly">This Month</option>
                <option value="custom">Custom</option>
            </select>
        </div>

        @if ($dateFilter === 'custom')
            <div>
                <label class="block text-sm mb-1 font-medium">From</label>
                <input type="date" wire:model.live="fromDate" class="border px-3 py-1 rounded text-sm" />
            </div>

            <div>
                <label class="block text-sm mb-1 font-medium">To</label>
                <input type="date" wire:model.live="toDate" class="border px-3 py-1 rounded text-sm" />
            </div>
        @endif

        <div>
            <label class="block text-sm mb-1 font-medium">Payment Method</label>
            <select wire:model="methodFilter" class="border px-3 py-1 rounded text-sm">
                <option value="">All</option>
                <option value="cash">Cash</option>
                <option value="upi">UPI</option>
                <option value="card">Card</option>
                <option value="duo">Duo</option>
                <option value="part">Part</option>
            </select>
        </div>

        <div class="ml-auto flex gap-2 mt-4 md:mt-0">
            <button wire:click="exportExcel" class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Export
                Excel</button>
            <button wire:click="exportPdf" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Export
                PDF</button>
        </div>
    </div>

    <div class="overflow-auto bg-white shadow rounded-lg">
        <table class="min-w-full text-sm text-left border">
            <thead class="bg-gray-100 text-gray-700">
                <tr>
                    <th class="p-2">#</th>
                    <th class="p-2">Order ID</th>
                    <th class="p-2">Customer</th>
                    <th class="p-2">Method</th>
                    <th class="p-2">Amount</th>
                    <th class="p-2">Paid Logs</th>
                    <th class="p-2">Remaining</th>
                    <th class="p-2">Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse($payments as $index => $payment)
                    @php
                        $firstLog = $payment->logs->first();
                        $remaining = $firstLog?->amount ?? 0;
                    @endphp
                    <tr class="border-t border-gray-300">
                        <td class="p-2">{{ $loop->iteration }}</td>
                        <td class="p-2">{{ $payment->order?->order_number ?? '-' }}</td>
                        <td class="p-2">{{ $payment->customer->name ?? ($firstLog?->customer_name ?? '—') }}</td>
                        <td class="p-2">{{ ucfirst($payment->method) }}</td>
                        <td class="p-2">₹{{ $payment->amount }}</td>
                        <td class="p-2">
                            ₹{{ number_format($payment->logs->sum('paid_amount'), 2) }}
                        </td>
                        <td class="px-6 py-3 text-sm text-red-500">
                            ₹{{ number_format($remaining, 2) }}
                        </td>
                        <td class="p-2">
                            @if ($payment->method === 'duo')
                                <button wire:click="showPaymentLogs({{ $payment->id }})" wire:loading.attr="disabled"
                                    class="text-blue-600 hover:underline text-sm">
                                    {{ $showLogsForPaymentId === $payment->id ? 'Hide' : 'Show' }}
                                </button>
                            @endif
                        </td>
                    </tr>

                    @if ($showLogsForPaymentId === $payment->id)
                        <tr class="bg-gray-100">
                            <td colspan="7" class="p-3">
                                @if (!empty($paymentLogs))
                                    <table class="min-w-full text-sm text-left border border-gray-300 rounded">
                                        <thead class="bg-white text-gray-700">
                                            <tr>
                                                <th class="border px-3 py-2">#</th>
                                                <th class="border px-3 py-2">Paid Amount</th>
                                                <th class="border px-3 py-2">Method</th>
                                                <th class="border px-3 py-2">Created At</th>
                                                <th class="border px-3 py-2">Issue</th>
                                                {{-- <th class="border px-3 py-2">Mobile</th> --}}
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($paymentLogs as $index => $log)
                                                <tr class="bg-white">
                                                    <td class="border px-3 py-2">{{ $index + 1 }}</td>
                                                    <td class="border px-3 py-2 text-green-600 font-medium">
                                                        ₹{{ $log['paid_amount'] }}</td>
                                                    <td class="border px-3 py-2 capitalize">{{ $log['method'] }}</td>
                                                    <td class="border px-3 py-2">
                                                        {{ \Carbon\Carbon::parse($log['created_at'])->format('d M Y H:i') }}
                                                    </td>
                                                    <td class="border px-3 py-2">{{ $log['issue'] ?? '-' }}</td>
                                                    {{-- <td class="border px-3 py-2">{{ $log['mobile'] ?? '-' }}</td> --}}
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <div class="text-sm text-gray-600">No logs found.</div>
                                @endif
                            </td>
                        </tr>
                    @endif

                @empty
                    <tr>
                        <td colspan="7" class="p-2 text-center">No records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $payments->links() }}
    </div>
</div>
