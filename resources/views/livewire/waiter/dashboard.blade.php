<div class="p-4" x-data="{ selectedTable: null, showModal: false }">
    <x-form.error />
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
        @foreach ($tables as $table)
            @php
                $statusColor = match ($table->status) {
                    'available' => 'bg-green-200',
                    'occupied' => 'bg-red-200',
                    'reserved' => 'bg-yellow-200',
                    default => 'bg-gray-400',
                };
            @endphp

            <div wire:click="openConfirm({{ $table->id }})"
                class="relative p-4 text-black rounded shadow cursor-pointer {{ $statusColor }}">

                <div class="absolute top-2 right-2 text-xs bg-white text-black px-2 py-0.5 rounded">
                    {{ $table->capacity }} Seats
                </div>
                <div class="text-lg font-bold">{{ $table->name }}</div>
                @if (setting('area_module'))
                    <div class="text-sm mt-1 italic">{{ $table->area->name ?? '' }}</div>
                @endif
                <div class="text-sm mt-1 italic">{{ ucfirst($table->status) }}</div>
            </div>
        @endforeach
    </div>

    @if ($showConfirm && $selectedTable)
        <div
            class="fixed inset-0 bg-transparet backdrop-blur-sm bg-opacity-40 backdrop-blur-sm flex items-center justify-center z-50">
            <div class="bg-white p-6 rounded-lg shadow-xl w-100 max-w-sm">

                @if ($selectedTable->status == 'available')
                    <div class="text-center mb-4">
                        <h2 class="text-xl font-semibold text-center mb-4">This table is available for booking.</h2>
                        <a href="{{ route('waiter.item', ['table_id' => $selectedTable->id]) }}"
                            class="block w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded text-center">
                            Book Now
                        </a>
                        <button wire:click="$set('showConfirm', false)"
                            class="mt-4 w-full py-2 bg-gray-500 text-white rounded">
                            Close
                        </button>
                    </div>
                @else
                    <div class="text-center mb-4">
                        <h2 class="text-gray-700">Sorry,This table is currently {{ $selectedTable->status }}.</h2>
                    </div>
                    <button wire:click="$set('showConfirm', false)"
                        class="mt-4 w-full py-2 bg-gray-500 text-white rounded">
                        Close
                    </button>
                @endif

            </div>
        </div>
    @endif

</div>
