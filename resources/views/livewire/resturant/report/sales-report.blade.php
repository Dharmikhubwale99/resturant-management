<div class="p-4 max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Sales Report</h1>

    <div class="flex flex-col sm:flex-row sm:space-x-4 space-y-2 sm:space-y-0 mb-4">
        <select wire:model.live="filterType" class="p-2 border rounded w-full sm:w-auto">
            <option value="today">Today</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
            <option value="custom">Custom</option>
        </select>
        @if ($filterType === 'custom')
            <input type="date" wire:model.live="fromDate" class="p-2 border rounded w-full sm:w-auto">
            <input type="date" wire:model.live="toDate" class="p-2 border rounded w-full sm:w-auto">
        @endif
    </div>

    <div class="bg-white border border-blue-500 rounded-md shadow p-4 mb-4 w-full max-w-xl mx-auto text-center">
        <p class="text-sm font-medium text-gray-700">
            <strong>Duration :</strong> From {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} to
            {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}
        </p>
        <p class="mt-1">Total Sales: {{ $orders->count() }}</p>
        {{-- <p>Total Sale Quantity: {{ $orders->sum('total_qty') ?? 0 }}</p> --}}
        <p>Total Sale Amount: ₹{{ number_format($this->totalAmount, 2) }}</p>
    </div>

    <div class="flex space-x-2 mb-4">
        <x-form.button type="button" title="Export to Excel" wireClick="exportExcel" wireTarget="exportExcel"
            class="bg-green-500 text-white px-4 py-2 rounded" />
        <x-form.button type="button" title="Export to PDF" wireClick="exportPdf" wireTarget="exportPdf"
            class="bg-blue-500 text-white px-4 py-2 rounded" />
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
            <thead class="bg-orange-400 text-black text-center">
                <tr>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Sr No</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Date</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Receipt No</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Party Name</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Party Phone</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Total Quantity</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Total Amount (incl. taxes)</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Created By</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($orders as $index => $order)
                    <tr class="hover:bg-gray-50 text-sm text-center">
                        <td class="px-4 py-2 whitespace-nowrap">{{ $index + 1 }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($order->created_at)->format('d-m-Y') }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $order->order_number ?? '-' }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            {{ $order->customer_name ?? ($order->table->name ?? 'N/A') }}
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $order->mobile ?? '-' }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $order->total_qty ?? '-' }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">₹{{ number_format($order->total_amount, 2) }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $order->user->name ?? 'Admin' }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <button wire:click="toggleOrderDetails({{ $order->id }})"
                                class="bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700">
                                {{ $selectedOrderId === $order->id ? 'Hide' : 'View Details' }}
                            </button>
                        </td>
                    </tr>
                    @if ($selectedOrderId === $order->id)
                        <tr>
                            <td colspan="8" class="p-0">
                                <table class="w-full border-t text-sm bg-gray-100">
                                    <thead class="bg-gray-200 text-left">
                                        <tr>
                                            <th class="px-4 py-2">#</th>
                                            <th class="px-4 py-2">Item Name</th>
                                            <th class="px-4 py-2">Qty</th>
                                            <th class="px-4 py-2">Unit Price</th>
                                            <th class="px-4 py-2">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($order->items as $key => $item)
                                            <tr class="border-t">
                                                <td class="px-4 py-2">{{ $key + 1 }}</td>
                                                <td class="px-4 py-2">{{ $item->item->name ?? 'N/A' }}</td>
                                                <td class="px-4 py-2">{{ $item->quantity ?? 0 }}</td>
                                                <td class="px-4 py-2">₹{{ number_format($item->base_price ?? 0, 2) }}
                                                </td>
                                                <td class="px-4 py-2">
                                                    ₹{{ number_format($item->total_price ?? $item->quantity * $item->base_price, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-gray-500">No sales records found.</td>
                    </tr>

                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</div>
