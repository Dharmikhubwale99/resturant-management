<div class="p-6 max-w-7xl mx-auto space-y-6">
    <h1 class="text-2xl font-bold mb-4">Staff Wise Sales Report</h1>

    <div class="flex justify-end mb-4">
        <input type="text" wire:model.debounce.500ms="search" placeholder="Search by name or phone"
            class="border p-2 rounded w-full sm:w-1/3" />
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border border-gray-300 mb-6">
            <thead class="bg-blue-600 text-white">
                <tr>
                    <th class="px-4 py-2 text-sm font-semibold">#</th>
                    <th class="px-4 py-2 text-sm font-semibold">Name</th>
                    <th class="px-4 py-2 text-sm font-semibold">Phone</th>
                    <th class="px-4 py-2 text-sm font-semibold">Total Orders</th>
                    <th class="px-4 py-2 text-sm font-semibold">Total Sales (₹)</th>
                    <th class="px-4 py-2 text-sm font-semibold">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">

                @forelse($staffList as $staff)
                    <tr>
                        <td class="px-4 py-2">{{ $loop->iteration }}</td>
                        <td class="px-4 py-2">{{ $staff->name }}</td>
                        <td class="px-4 py-2">{{ $staff->mobile ?? '-' }}</td>
                        <td class="px-4 py-2">{{ $staff->total_orders ?? 0 }}</td>
                        <td class="px-4 py-2">₹{{ number_format($staff->total_sales ?? 0, 2) }}</td>
                        <td class="px-4 py-2">
                            <button wire:click="showDetails({{ $staff->id }})"
                                class="bg-indigo-600 text-white px-3 py-1 rounded hover:bg-indigo-700">
                                {{ $selectedUserId === $staff->id ? 'Hide' : 'View Details' }}
                            </button>
                        </td>
                    </tr>

                    @if ($selectedUserId === $staff->id && count($orders))
                        <tr>
                            <td colspan="5" class="bg-gray-100 p-4">
                                <h2 class="text-md font-semibold mb-2 text-gray-800">Order Details</h2>
                                <table class="w-full table-auto border">
                                    <thead class="bg-gray-200 text-gray-700 text-sm">
                                        <tr>
                                            <th class="px-3 py-2">Order No</th>
                                            <th class="px-3 py-2">Date</th>
                                            <th class="px-3 py-2">Customer</th>
                                            <th class="px-3 py-2">Item</th>
                                            <th class="px-3 py-2">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody class="text-sm">
                                        @foreach ($orders as $order)
                                            <tr class="border-t hover:bg-gray-50">
                                                <td class="px-3 py-2">{{ $order->order_number }}</td>
                                                <td class="px-3 py-2">
                                                    {{ \Carbon\Carbon::parse($order->created_at)->format('d-m-Y') }}
                                                </td>
                                                <td class="px-3 py-2">
                                                    {{ $order->customer_name ?? ($order->table->name ?? 'N/A') }}</td>
                                                <td class="px-3 py-2">
                                                    @foreach ($order->items as $item)
                                                        <div>{{ $item->item->name ?? 'N/A' }}</div>
                                                    @endforeach
                                                </td>
                                                <td class="px-3 py-2">₹{{ number_format($order->total_amount, 2) }}
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
