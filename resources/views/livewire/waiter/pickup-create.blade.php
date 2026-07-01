<div class="min-h-screen bg-gray-100 p-4">

    <div class="max-w-8xl mx-auto bg-white rounded shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Takeaway Orders</h2>
            <button class="btn btn-sm bg-blue-500 text-white rounded px-3 py-1" wire:click="showCustomerForm">Add
                Pickup</button>
        </div>

        <div class="overflow-x-auto">
            <table class="table-auto w-full text-sm">
                <thead>
                    <tr class="bg-gray-200 text-left">
                        <th class="p-2 whitespace-nowrap">Order ID</th>
                        <th class="p-2 whitespace-nowrap">Customer Name</th>
                        <th class="p-2 whitespace-nowrap">Mobile</th>
                        <th class="p-2 whitespace-nowrap">Amount</th>
                        <th class="p-2 whitespace-nowrap">Created At</th>
                        <th class="p-2 whitespace-nowrap">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($takeawayOrders as $order)
                        <tr class="border-b">
                            <td class="p-2 whitespace-nowrap">{{ $order->id }}</td>
                            <td class="p-2 whitespace-nowrap">{{ $order->customer_name }}</td>
                            <td class="p-2 whitespace-nowrap">{{ $order->mobile }}</td>
                            <td class="p-2 whitespace-nowrap">{{ $order->total_amount }}</td>
                            <td class="p-2 whitespace-nowrap">{{ $order->created_at->format('d-m-Y H:i') }}</td>
                            <td class="p-2 whitespace-nowrap">
                                <button wire:click.stop="editTable({{ $order->id }})"
                                    class="bg-white p-2 rounded-full hover:bg-gray-100 transition-colors"
                                    title="View / Edit order">
                                    View
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center p-4">No takeaway orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($customer_form)
        <div class="fixed inset-0 bg-transparent bg-blur bg-opacity-30 flex items-center justify-center">
            <div class="bg-white p-6 rounded shadow max-w-md w-full">
                <h2 class="text-lg font-bold mb-4">Add Pickup</h2>
                <x-form.error />
                <form wire:submit.prevent="submit" class="space-y-3">

                    <x-form.input name="customer_name" label="Name" wireModel="customer_name" required
                        placeholder="Enter name" />
                    <x-form.input name="mobile" label="Phone" wireModel="mobile" placeholder="Enter phone number" />

                    <div class="flex justify-end space-x-2">
                        <x-form.button type="button" wireClick="hideCustomerForm" title="Cancel"
                            class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-semibold py-2 rounded-lg transition duration-200 shadow-md" />
                        <x-form.button type="submit" title="Save" wireTarget="submit" />
                    </div>
                </form>
            </div>
        </div>
    @endif

</div>
