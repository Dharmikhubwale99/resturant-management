<div class="p-6 bg-white rounded shadow">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 sm:gap-4 mb-4">
        <h2 class="text-xl font-bold truncate">Money In</h2>
        <div class="w-full sm:w-auto flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
            <div class="flex flex-row gap-3">
                <x-form.input type="date" name="from_date" wireModelLive="fromDate" wrapperClass="mb-0 w-full sm:w-auto"
                    inputClass="w-full sm:w-auto border border-gray-300 focus:ring focus:ring-blue-300" />

                <x-form.input type="date" name="to_date" wireModelLive="toDate" wrapperClass="mb-0 w-full sm:w-auto"
                    inputClass="w-full sm:w-auto border border-gray-300 focus:ring focus:ring-blue-300" />

            </div>
            <x-form.input name="search" placeholder="Search .." wireModelLive="search"
                wrapperClass="mb-0 w-full sm:w-auto"
                inputClass="w-full sm:w-72 border border-gray-300 focus:ring focus:ring-blue-300" />

            @can('party-create')
                @if (setting('moneyIn'))
                    <x-form.button :route="'restaurant.money-in.create'">
                        + Add
                    </x-form.button>
                @endif
            @endcan
        </div>
    </div>

    <x-form.error />
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">#</th>
                    <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Order</th>
                    <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Customer</th>
                    <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Mobile</th>
                    <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Method</th>
                    <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Paid</th>
                    <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Remaining</th>
                    <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Issue</th>
                    <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                    <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($logs as $payment)
                    @php
                        $firstLog = $payment->logs->first();
                        $remaining = $firstLog?->amount ?? 0;
                    @endphp
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 whitespace-nowrap py-3 text-sm text-gray-900">{{ $payment->id ?? '—' }}</td>
                        <td class="px-6 whitespace-nowrap py-3 text-sm text-gray-900">
                            {{ $payment->order->order_number ?? '—' }}</td>
                        <td class="px-6 whitespace-nowrap py-3 text-sm text-gray-900">
                            {{ $payment->customer->name ?? ($firstLog?->customer_name ?? '—') }}
                        </td>
                        <td class="px-6 whitespace-nowrap py-3 text-sm text-gray-900">
                            {{ $payment->customer->mobile ?? ($firstLog?->mobile ?? '—') }}
                        </td>
                        <td class="px-6 whitespace-nowrap py-3 text-sm text-gray-900">{{ ucfirst($payment->method) }}
                        </td>
                        <td class="px-6 whitespace-nowrap py-3 text-sm text-gray-900">
                            @if ($payment->method === 'duo')
                                ₹{{ number_format($payment->logs->sum('paid_amount'), 2) }}
                            @else
                                ₹{{ number_format($payment->amount, 2) }}
                            @endif
                        </td>
                        <td class="px-6 whitespace-nowrap py-3 text-sm text-red-500">
                            ₹{{ number_format($remaining, 2) }}
                        </td>
                        <td class="px-6 whitespace-nowrap py-3 text-sm text-gray-900">{{ $firstLog?->issue ?? '—' }}
                        </td>
                        <td class="px-6 whitespace-nowrap py-3 text-sm text-gray-900">
                            {{ $payment->payment_date ?? ($firstLog?->created_at?->format('d-m-Y H:i') ?? '—') }}</td>
                        <td class="px-6 whitespace-nowrap py-3 text-sm text-gray-900">
                            @if ($remaining > 0)
                                <button wire:click="openPaymentModal({{ $firstLog->id }})"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                    Pay Remaining
                                </button>
                            @else
                                <span class="text-green-600 font-semibold">Paid</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">
        {{ $logs->links() }}
    </div>
    @if ($showModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
            <div class="bg-white w-full max-w-md p-6 rounded shadow-lg">
                <h2 class="text-lg font-bold mb-4">Pay Remaining Amount</h2>

                <div class="mb-3">
                    <label class="block text-sm mb-1">Paid Amount</label>
                    <input type="number" step="0.01" wire:model.defer="newPaidAmount"
                        class="w-full border rounded px-2 py-1" />
                    @error('newPaidAmount')
                        <span class="text-red-600 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="block text-sm mb-1">Payment Method</label>
                    <select wire:model.defer="newMethod" class="w-full border rounded px-2 py-1">
                        <option value="">Select</option>
                        <option value="cash">Cash</option>
                        <option value="upi">UPI</option>
                        <option value="card">Card</option>
                    </select>
                    @error('newMethod')
                        <span class="text-red-600 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="block text-sm mb-1">Remarks / Issue</label>
                    <textarea wire:model.defer="newIssue" class="w-full border rounded px-2 py-1"></textarea>
                </div>

                <div class="flex justify-end gap-2">
                    <button wire:click="$set('showModal', false)" class="px-3 py-1 border rounded">Cancel</button>
                    <button wire:click="saveFollowUpPayment"
                        class="bg-green-600 text-white px-4 py-1 rounded">Save</button>
                </div>
            </div>
        </div>
    @endif

</div>
