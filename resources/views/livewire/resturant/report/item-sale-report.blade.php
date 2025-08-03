<div class="p-4 max-w-7xl mx-auto">
    <h2 class="text-2xl font-bold mb-4">Item Sale Report</h2>

    <div class="flex flex-col sm:flex-row sm:space-x-4 space-y-2 sm:space-y-0 mb-4">
        <select wire:model.live="filterType" class="p-2 border rounded w-full sm:w-auto">
            <option value="today">Today</option>
            <option value="weekly">This Week</option>
            <option value="monthly">This Month</option>
            <option value="custom">Custom</option>
        </select>

        @if ($filterType === 'custom')
            <input type="date" wire:model.live="fromDate" class="p-2 border rounded w-full sm:w-auto" />
            <input type="date" wire:model.live="toDate" class="p-2 border rounded w-full sm:w-auto" />
        @endif
    </div>

    <div class="bg-white border border-blue-500 rounded-md shadow p-4 mb-4 w-full max-w-xl mx-auto text-center">
        <p class="text-sm font-medium text-gray-700">
            <strong>Duration :</strong> From {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }} to
            {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}
        </p>
        <p>Total Sale Quantity: <strong>{{ $data->sum('total_qty') }}</strong></p>
        <p>Total Sale Amount: ₹<strong>{{ number_format($data->sum('total_amount'), 2) }}</strong></p>
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
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Item Name</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Category</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Total Sale Quantity</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Total Sale Amount</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-center">
                @forelse($data as $index => $item)
                    <tr class="hover:bg-gray-50 text-sm">
                        <td class="px-4 py-2 whitespace-nowrap">{{ $index + 1 }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $item->item_name }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $item->category_name ?? 'N/A' }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $item->total_qty }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">₹{{ number_format($item->total_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-2 text-sm text-gray-500">No data available</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $data->links() }}
    </div>
</div>
