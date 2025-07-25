<div class="p-4 max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Payment Report</h1>

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

        <select wire:model.live="paymentMethod" class="p-2 border rounded w-full sm:w-auto">
            <option value="all">All Methods</option>
            <option value="cash">Cash</option>
            <option value="card">Card</option>
            <option value="upi">UPI</option>
            <option value="part">Part</option>
            <option value="duo">Duo</option>
        </select>
    </div>

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
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Payment No</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Amount</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Method</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Customer Name</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Mobile</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($payments as $row)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-2 text-sm text-gray-900 whitespace-nowrap">{{ $row->id }}</td>
                        <td class="px-6 py-2 text-sm text-gray-900 whitespace-nowrap">{{ \Carbon\Carbon::parse($row->created_at)->format('d-m-Y') }}</td>
                        <td class="px-6 py-2 text-sm text-gray-900 whitespace-nowrap">₹{{ number_format($row->amount, 2) }}</td>
                        <td class="px-6 py-2 text-sm text-gray-900 whitespace-nowrap">{{ ucfirst($row->method) }}</td>
                        <td class="px-6 py-2 text-sm text-gray-900 whitespace-nowrap">-</td>
                        <td class="px-6 py-2 text-sm text-gray-900 whitespace-nowrap">-</td>
                    </tr>
                @empty
                @endforelse
                @forelse($logs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-2 text-sm text-gray-900 whitespace-nowrap">{{ $log->id }}</td>
                        <td class="px-6 py-2 text-sm text-gray-900 whitespace-nowrap">{{ \Carbon\Carbon::parse($log->created_at)->format('d-m-Y') }}</td>
                        <td class="px-6 py-2 text-sm text-gray-900 whitespace-nowrap">₹{{ number_format($log->amount, 2) }}</td>
                        <td class="px-6 py-2 text-sm text-gray-900 whitespace-nowrap">{{ ucfirst($log->method) }}</td>
                        <td class="px-6 py-2 text-sm text-gray-900 whitespace-nowrap">{{ $log->customer_name }}</td>
                        <td class="px-6 py-2 text-sm text-gray-900 whitespace-nowrap">{{ $log->mobile }}</td>
                    </tr>
                @empty
                @endforelse
                @if(count($payments) == 0 && count($logs) == 0)
                    <tr>
                        <td colspan="6" class="px-6 py-3 text-sm text-gray-900 text-center">No records found.</td>
                    </tr>
                @endif
            </tbody>
            <tfoot>
                <tr class="bg-gray-100 font-bold">
                    <td colspan="5" class="px-6 py-2 text-right">Total</td>
                    <td class="px-6 py-2">₹{{ number_format($this->totalAmount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
 
    <div class="mt-4">
        {{ $payments->links() }}
    </div>
</div>
