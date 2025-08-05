<div>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Duo Payment Logs</h1>

        <table class="min-w-full border text-sm">
            <thead class="bg-gray-200">
                <tr>
                    <th class="px-4 py-2">Order #</th>
                    <th class="px-4 py-2">Customer</th>
                    <th class="px-4 py-2">Mobile</th>
                    <th class="px-4 py-2">Method</th>
                    <th class="px-4 py-2">Paid</th>
                    <th class="px-4 py-2">Remaining</th>
                    <th class="px-4 py-2">Issue</th>
                    <th class="px-4 py-2">Date</th>
                    <th class="px-4 py-2">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($logs as $log)
                @php
                $paymentKey = $log->payment_id;

                // if this is the first log for this payment id, mark it
                if (!array_key_exists($paymentKey, $firstLogForPaymentId)) {
                    $firstLogForPaymentId[$paymentKey] = $log->id;
                }

                $isFirstLog = $firstLogForPaymentId[$paymentKey] === $log->id;
            @endphp

                    <tr class="border-t text-center">
                        <td class="px-4 py-2">{{ $log->order->order_number ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $log->customer_name }}</td>
                        <td class="px-4 py-2">{{ $log->mobile }}</td>
                        <td class="px-4 py-2">{{ ucfirst($log->method) }}</td>
                        <td class="px-4 py-2">₹{{ number_format($log->paid_amount, 2) }}</td>
                        <td class="px-4 py-2 text-red-500">₹{{ number_format($log->amount, 2) }}</td>
                        <td class="px-4 py-2">{{ $log->issue ?? '—' }}</td>
                        <td class="px-4 py-2">{{ $log->created_at->format('d-m-Y H:i') }}</td>
                        <td class="px-4 py-2">
                            @if ($isFirstLog && $log->amount > 0)
                                <button wire:click="openPaymentModal({{ $log->id }})"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                    Pay Remaining
                                </button>
                            @elseif($log->amount <= 0)
                                <span class="text-green-600 font-semibold">Paid</span>
                            @else
                                <span class="text-gray-500">—</span>
                            @endif
                        </td>
                    </tr>
                @endforeach


            </tbody>
        </table>
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
