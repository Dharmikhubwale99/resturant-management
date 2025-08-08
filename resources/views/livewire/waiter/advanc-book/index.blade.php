<div class="p-6">
    <h2 class="text-2xl font-bold mb-6 text-gray-800">Reserved Tables</h2>

    @if($reservedTables->isEmpty())
        <div class="text-center text-gray-500 text-lg">
            No tables are currently reserved.
        </div>
    @else
        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">#</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Table</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Customer</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Reserved At</th>
                        <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($reservedTables as $index => $table)
                        <tr>
                            <td class="px-6 py-4 text-sm text-gray-800">{{ $index + 1 }}</td>
                            <td class="px-6 py-4 text-sm text-gray-800">{{ $table->table->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-800">{{ $table->customer->name }}</td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                {{ $table->booking_time ? \Carbon\Carbon::parse($table->booking_time)->format('d-m-Y, h:i A') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 text-sm">
                                <button wire:click="startOrder({{ $table->id }})"
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                                    Start Order
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>
