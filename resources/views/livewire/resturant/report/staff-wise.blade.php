<div class="p-6 max-w-7xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold mb-4">Staff Wise Sales Report</h1>

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 flex-wrap">
        <div class="flex flex-col sm:flex-row gap-2 flex-wrap">

            <select wire:model.live="dateFilter" class="border border-gray-300 p-2 rounded w-full sm:w-auto">
                <option value="today">Today</option>
                <option value="weekly">This Week</option>
                <option value="monthly">This Month</option>
                <option value="custom">Custom</option>
            </select>

            @if ($dateFilter === 'custom')
                <input type="date" wire:model.live="fromDate"
                    class="border border-gray-300 p-2 rounded w-full sm:w-auto">
                <span class="text-gray-600">to</span>
                <input type="date" wire:model.live="toDate"
                    class="border border-gray-300 p-2 rounded w-full sm:w-auto">
            @endif
        </div>

        <div class="flex flex-wrap gap-2">
            <x-form.button type="button" title="Export to Excel" wireClick="exportExcel" wireTarget="exportExcel"
                class="bg-green-500 text-white px-4 py-2 rounded" />
            <x-form.button type="button" title="Export to PDF" wireClick="exportPdf" wireTarget="exportPdf"
                class="bg-blue-500 text-white px-4 py-2 rounded" />
        </div>
        {{-- <input type="text" wire:model.debounce.500ms="search" placeholder="Search by name or phone"
            class="border p-2 rounded w-full sm:w-1/3" /> --}}
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border border-gray-300 mb-6">
            <thead class="bg-orange-400 text-black text-center">
                <tr>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">#</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Name</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Phone</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Total Orders</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Total Sales (₹)</th>
                    <th class="px-4 py-2 text-sm font-semibold whitespace-nowrap">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">

                @forelse($staffList as $staff)
                    <tr class="text-center">
                        <td class="px-4 py-2 whitespace-nowrap">{{ $loop->iteration }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $staff->name }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $staff->mobile ?? '-' }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">{{ $staff->total_orders ?? 0 }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">₹{{ number_format($staff->total_sales ?? 0, 2) }}</td>
                        <td class="px-4 py-2 whitespace-nowrap">
                            <button wire:click="showDetails({{ $staff->id }})"
                                class="bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700">
                                {{ $selectedUserId === $staff->id ? 'Hide' : 'View Details' }}
                            </button>
                        </td>
                    </tr>

                    @if ($selectedUserId === $staff->id && count($orders))
                        <tr>
                            <td colspan="5" class="items-center bg-gray-100 p-4 text-center">
                                <h2 class="text-md font-semibold mb-2 text-gray-800">Order Details</h2>
                                <table class="w-full table-auto border">
                                    <thead class="bg-gray-200 text-gray-700 text-sm">
                                        <tr>
                                            <th class="px-3 py-2 whitespace-nowrap">Order No</th>
                                            <th class="px-3 py-2 whitespace-nowrap">Date</th>
                                            <th class="px-3 py-2 whitespace-nowrap">Customer</th>
                                            <th class="px-3 py-2 whitespace-nowrap">Item</th>
                                            <th class="px-3 py-2 whitespace-nowrap">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm text-center">
                                        @foreach ($orders as $order)
                                            <tr class="border-t hover:bg-gray-50">
                                                <td class="px-3 py-2 whitespace-nowrap">{{ $order->order_number }}</td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    {{ \Carbon\Carbon::parse($order->created_at)->format('d-m-Y') }}
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    {{ $order->customer_name ?? ($order->table->name ?? 'N/A') }}</td>
                                                <td class="px-3 py-2 whitespace-nowrap">
                                                    @foreach ($order->items as $item)
                                                        <div>{{ $item->item->name ?? 'N/A' }}</div>
                                                    @endforeach
                                                </td>
                                                <td class="px-3 py-2 whitespace-nowrap">₹{{ number_format($order->total_amount, 2) }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    @else
                        @if ($selectedUserId === $staff->id)
                            <tr>
                                <td colspan="5" class="text-center py-4">No orders found for this staff.</td>
                            </tr>
                        @endif
                    @endif
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4">No staff found.</td>
                    </tr>
                @endforelse

            </tbody>
        </table>
    </div>

    <div>
        {{ $staffList->links() }}
    </div>
</div>
