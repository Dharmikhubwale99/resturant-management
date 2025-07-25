<div class="p-4 lg:p-6 space-y-4">
    @php
        $tabs = ['pending' => 'Pending', 'preparing' => 'Preparing', 'ready' => 'Ready', 'served' => 'Served'];
        $statusColors = [
            'pending' => 'bg-yellow-200 text-yellow-800',
            'preparing' => 'bg-blue-200 text-blue-800',
            'ready' => 'bg-green-200 text-green-800',
            'served' => 'bg-gray-300 text-gray-700',
        ];
    @endphp

    <!-- Tabs -->
    <div class="border-b border-gray-200 flex gap-6 text-sm font-medium">
        @foreach ($tabs as $key => $label)
            <button wire:click="setStatus('{{ $key }}')"
                @class([
                    'pb-2',
                    $status === $key ? 'border-b-2 border-red-500 text-red-600' : 'text-gray-600 hover:text-gray-800',
                ])>
                {{ $label }}
            </button>
        @endforeach
    </div>

    <!-- Orders List -->
    @if (empty($orders))
        <p class="text-gray-500">No {{ ucfirst($status) }} orders 🔍</p>
    @else
        <div class="space-y-3">
            @foreach ($orders as $order)
                <div class="bg-white border rounded shadow-sm p-3 text-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex flex-wrap gap-x-6">
                            <span class="font-semibold">#{{ $order['kot_number'] }}</span>
                            <span>Table: {{ $order['table_name'] }} ({{ $order['area_name'] }})</span>
                            <span>Date: {{ \Carbon\Carbon::parse($order['created_at'])->format('d-m-Y') }}</span>
                            <span>Qty: {{ $order['items_count'] }}</span>
                            <span>Time: {{ \Carbon\Carbon::parse($order['created_at'])->format('h:i A') }}</span>
                            <span
                                class="text-xs px-2 py-0.5 rounded-full {{ $statusColors[$order['status']] ?? 'bg-gray-100' }}">
                                {{ ucfirst($order['status']) }}
                            </span>
                        </div>

                        <button wire:click="toggleShow({{ $order['id'] }})"
                            class="text-blue-600 hover:underline">
                            {{ isset($openItems[$order['id']]) ? 'Hide' : 'Show' }}
                        </button>
                    </div>

                    @isset($openItems[$order['id']])
                        <div class="mt-3 border-t pt-2 space-y-1">
                            @foreach ($openItems[$order['id']] as $item)
                                <div class="flex justify-between">
                                    <span>{{ $item->item->name }} × {{ $item->quantity }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endisset
                </div>
            @endforeach
        </div>
    @endif
</div>
