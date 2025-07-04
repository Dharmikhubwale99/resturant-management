<div class="p-4 lg:p-6">
    <h1 class="text-xl font-bold mb-4">Pending KOT Orders</h1>

    @if($orders->isEmpty())
        <p class="text-gray-500">📭 All clear — no pending orders!</p>
    @else
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-100 font-semibold text-gray-700">
                <tr>
                    <th class="px-3 py-2 text-left">#</th>
                    <th class="px-3 py-2 text-left">Table / Area</th>
                    <th class="px-3 py-2 text-left">Items Qty</th>
                    <th class="px-3 py-2 text-left">Amount</th>
                    <th class="px-3 py-2 text-left">Created At</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-200">
                @foreach($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2">{{ $order->id }}</td>
                        <td class="px-3 py-2">
                            {{ $order->table->name ?? '—' }}
                            <span class="text-xs text-gray-500">
                                {{ $order->table->area->name ?? '' }}
                            </span>
                        </td>
                        <td class="px-3 py-2">{{ $order->items_count ?? '—' }}</td>
                        <td class="px-3 py-2">₹{{ number_format($order->total_amount,2) }}</td>
                        <td class="px-3 py-2">{{ $order->created_at->format('d-M H:i') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>
