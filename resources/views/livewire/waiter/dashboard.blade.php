<div class="p-4" x-data="{ selectedTable: null, showModal: false }">
    <x-form.error />

    <div class="flex justify-end gap-6 mb-8">
         <a href="{{ route('waiter.pickup.create') }}">
            <button
                class="bg-red-500 text-white px-3 py-1 md:px-4 md:py-2 rounded hover:bg-red-600 text-sm md:text-base">
                Pick Up
            </button>
        </a>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-green-300 rounded"></div>
            <span class="text-sm font-medium text-gray-700">Available</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-red-300 rounded"></div>
            <span class="text-sm font-medium text-gray-700">Occupied</span>
        </div>
        <div class="flex items-center gap-2">
            <div class="w-4 h-4 bg-yellow-300 rounded"></div>
            <span class="text-sm font-medium text-gray-700">Reserved</span>
        </div>
   
    </div>
    @foreach ($tablesByArea as $areaName => $tables)
        <h2 class="text-xl font-bold mb-2 mt-6">{{ $areaName }}</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4 mb-8">
            @foreach ($tables as $table)
                @php
                    $statusColor = match ($table->status) {
                        'available' => 'bg-green-200',
                        'occupied' => 'bg-red-200',
                        'reserved' => 'bg-yellow-200',
                        default => 'bg-gray-400',
                    };
                @endphp

                <div class="table-card rounded-2xl p-6 cursor-pointer relative {{ $statusColor }}
                transition-transform transform hover:scale-105 hover:shadow-xl"
                    wire:click="openConfirm({{ $table->id }})">

                    <div
                        class="absolute top-4 right-4 bg-white px-3 py-1 rounded-full text-xs font-medium text-gray-700">
                        {{ $table->capacity }} Seats
                    </div>

                    <div class="text-2xl font-bold text-gray-800 mb-2">{{ $table->name }}</div>
                    @if (setting('area_module'))
                        <div class="text-sm text-black mb-1">{{ $table->area->name ?? '' }}</div>
                    @endif
                    <div class="text-sm text-black mb-1">{{ ucfirst($table->status) }}</div>

                    @if ($table->status === 'occupied')
                        <div class="absolute bottom-4 right-4 flex items-center gap-2">

                            <button wire:click.stop="editTable({{ $table->id }})"
                                class="bg-white p-2 rounded-full hover:bg-gray-100 transition-colors"
                                title="View / Edit order">
                                <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                    </path>
                                </svg>
                            </button>

                            <div class="bg-white px-2 py-1 rounded-full flex items-center gap-1">
                                <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <polyline points="12,6 12,12 16,14"></polyline>
                                </svg>
                                <span class="text-xs font-medium text-gray-700">
                                    {{ \Carbon\Carbon::parse($table->updated_at)->diffForHumans(null, true) }}
                                </span>
                            </div>

                        </div>
                    @endif
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
