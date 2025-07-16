<div class="p-4 max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Sales Report</h1>

    <div class="flex flex-col sm:flex-row sm:space-x-4 space-y-2 sm:space-y-0 mb-4">
        <select wire:model.live="filterType" class="p-2 border rounded w-full sm:w-auto">
            <option value="today">Today</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
            <option value="custom">Custom</option>
        </select>

        @if($filterType === 'custom')
            <input type="date" wire:model.live="fromDate" class="p-2 border rounded w-full sm:w-auto">
            <input type="date" wire:model.live="toDate" class="p-2 border rounded w-full sm:w-auto">
        @endif
    </div>

    <!-- Export Button -->
    <button wire:click="exportExcel" class="bg-green-500 text-white px-4 py-2 rounded mb-4">
        Export to Excel
    </button>
    <button wire:click="exportPdf" class="bg-blue-500 text-white px-4 py-2 rounded mb-4">
        Export to PDF
    </button>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
        <thead class="bg-gray-100">
            <tr>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Order No</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Total Amount</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200">
            @forelse($orders as $order)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-2 text-sm text-gray-900 whitespace-nowrap">{{ $order->id }}</td>
                    <td class="px-6 py-2 text-sm text-gray-900 whitespace-nowrap">{{ $order->created_at->format('d-m-Y') }}</td>
                    <td class="px-6 py-2 text-sm text-gray-900 whitespace-nowrap">₹{{ number_format($order->total_amount, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="px-6 py-3 text-sm text-gray-900 text-center">No orders found.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
    <tr class="bg-gray-100 font-bold">
        <td colspan="2" class="px-6 py-2 text-right">Total</td>
        <td class="px-6 py-2">₹{{ number_format($this->totalAmount, 2) }}</td>
    </tr>
</tfoot>
    </table>
    </div>
 
    <div class="mt-4">
        {{ $orders->links() }}
    </div>
</div>
