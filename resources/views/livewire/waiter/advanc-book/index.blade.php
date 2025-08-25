<div class="p-6">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
        <h2 class="text-2xl font-bold text-gray-800">Reserved Tables</h2>
        <div class="flex flex-col sm:flex-row sm:items-center gap-3 w-full md:w-auto">

            <x-form.input name="search" placeholder="Search by name or mobile…" wireModelLive="search" wrapperClass="mb-0"
                inputClass="w-72" />
            <x-form.button title="+ Add" route="restaurant.advance-booking.create"
                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2" />
        </div>
    </div>

    @if ($bookings->isEmpty())
        <div class="text-center text-gray-500 text-lg">
            No tables are currently reserved.
        </div>
    @else
        <div class="overflow-x-auto bg-white shadow rounded-lg">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">#</th>
                        <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Table</th>
                        <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Customer
                        </th>
                        <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Mobile
                        </th>
                        <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Reserved
                            At</th>
                        <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Action
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($bookings as $index => $booking)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 whitespace-nowrap py-4 text-sm text-gray-800">
                                {{ ($bookings->currentPage() - 1) * $bookings->perPage() + $loop->iteration }}
                            </td>
                            <td class="px-6 whitespace-nowrap py-4 text-sm text-gray-800">
                                {{ $booking->table->name ?? '—' }}
                            </td>
                            <td class="px-6 whitespace-nowrap py-4 text-sm text-gray-800">
                                {{ $booking->customer->name ?? '—' }}
                            </td>
                            <td class="px-6 whitespace-nowrap py-4 text-sm text-gray-800">
                                {{ $booking->customer->mobile ?? '—' }}
                            </td>
                            <td class="px-6 whitespace-nowrap py-4 text-sm text-gray-500">
                                {{ $booking->booking_time ? \Carbon\Carbon::parse($booking->booking_time)->format('d-m-Y, h:i A') : 'N/A' }}
                            </td>
                            <td class="px-6 whitespace-nowrap py-4 text-sm">
                                <button wire:click="startOrder({{ $booking->id }})"
                                    class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded">
                                    Start Order
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="p-3">
                {{ $bookings->links() }}
            </div>
        </div>
    @endif
</div>
