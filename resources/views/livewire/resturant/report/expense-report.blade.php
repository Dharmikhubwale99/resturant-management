<div class="p-4 max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Expense Report</h1>

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
        <p class="mt-1">Total Expenses: {{ $expenses->count() }}</p>
        <p>Total Expense Amount: ₹{{ number_format($this->totalAmount, 2) }}</p>
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
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Party Name</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Expense Type</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Amount Paid</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 text-center">
                @forelse($expenses as $index => $expense)
                    <tr class="hover:bg-gray-50 text-sm">
                        <td class="px-4 py-2 whitespace-nowrap">{{ $index + 1 }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ \Carbon\Carbon::parse($expense->paid_at)->format('d-m-Y') }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $expense->name }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $expense->expenseType->name }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $expense->amount }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-3 text-center text-sm text-gray-500">No sales records found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $expenses->links() }}
    </div>
</div>
