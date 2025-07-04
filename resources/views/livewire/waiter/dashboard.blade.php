<div class="p-4" x-data="{ selectedTable: null, showModal: false }">
    <x-form.error />

    @foreach ($tablesByArea as $areaName => $tables)
        <h2 class="text-xl font-bold mb-2 mt-6">{{ $areaName }}</h2>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
            {{-- dashboard.blade.php  (only the table card block changes) --}}
            @foreach ($tables as $table)
                @php
                    $statusColor = match ($table->status) {
                        'available' => 'bg-green-200',
                        'occupied' => 'bg-red-200',
                        'reserved' => 'bg-yellow-200',
                        default => 'bg-gray-400',
                    };
                @endphp

                <div class="relative p-4 text-black rounded shadow {{ $statusColor }}" {{-- default click still opens confirmation --}}
                    wire:click="openConfirm({{ $table->id }})">

                    {{-- capacity badge --}}
                    <div class="absolute top-2 right-2 text-xs bg-white px-2 py-0.5 rounded">
                        {{ $table->capacity }} Seats
                    </div>

                    {{-- 👁 eye button only if occupied; stopPropagation so parent click not triggered --}}
                    @if ($table->status === 'occupied')
                        <button wire:click.stop="editTable({{ $table->id }})"
                            class="absolute top-2 left-2 text-gray-700 hover:text-gray-900" title="View / Edit order">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 fill-current" viewBox="0 0 24 24">
                                <path d="M12 5c-7 0-11 7-11 7s4 7 11 7 11-7 11-7-4-7-11-7zm0 12c-3 0-5-2-5-5s2-5 5-5
                 5 2 5 5-2 5-5 5zm0-8c-1.657 0-3 1.343-3 3s1.343 3 3 3 3-1.343
                 3-3-1.343-3-3-3z" />
                            </svg>
                        </button>
                    @endif

                    <div class="text-lg font-bold">{{ $table->name }}</div>
                    @if (setting('area_module'))
                        <div class="text-sm mt-1 italic">{{ $table->area->name ?? '' }}</div>
                    @endif
                    <div class="text-sm mt-1 italic">{{ ucfirst($table->status) }}</div>
                </div>
            @endforeach

        </div>
    @endforeach

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
