    <div class="p-4 md:p-6 bg-gray-100 min-h-screen">
        <div class="max-w-5xl mx-auto">

            {{-- Header --}}
            <div class="flex flex-col md:flex-row md:justify-between mb-6 gap-4">
                <h1 class="text-2xl font-bold">Kitchen Dashboard</h1>
                <select class="border rounded px-3 py-1 text-sm w-32 self-start md:self-auto">
                    <option>Today</option>
                    <option>All</option>
                </select>
            </div>

            {{-- Tabs --}}
            <div class="flex border-b mb-6 space-x-4 text-sm font-semibold">
                @foreach (['pending', 'preparing', 'ready', 'cancelled'] as $tab)
                    <button wire:click="$set('activeTab', '{{ $tab }}')"
                        class="pb-2 {{ $activeTab === $tab ? 'border-b-2 border-red-500 text-red-600' : 'text-gray-600' }}">
                        {{ ucfirst($tab) }}
                    </button>
                @endforeach
            </div>

            {{-- KOT Cards --}}
            <div class="grid gap-4 grid-cols-1 sm:grid-cols-2 lg:grid-cols-3">
                @php
                    $kotList = match ($activeTab) {
                        'pending' => $pendingKots,
                        'preparing' => $preparingKots,
                        'ready' => $readyKots,
                        'cancelled' => $cancelledKots,
                        default => [],
                    };

                    $colorMap = [
                        'pending' => 'gray-500',
                        'preparing' => 'yellow-500',
                        'ready' => 'green-500',
                        'cancelled' => 'red-500',
                    ];
                    $borderColor = $colorMap[$activeTab] ?? 'gray-400';
                @endphp

                @forelse ($kotList as $kot)
                    <div class="border-l-4 border-{{ $borderColor }} bg-white p-4 rounded shadow mb-4">
                        <p class="font-semibold">
                            {{ $kot->table->area->name ?? 'Area' }} – Table {{ $kot->table->name ?? $kot->table->id }}
                        </p>
                        <p class="text-sm text-gray-600">
                            KOT #{{ $kot->kot_number }}
                        </p>

                        <ul class="text-sm text-gray-700 mt-1 space-y-1">
                            @foreach ($kot->items as $item)
                                <li class="flex justify-between items-start gap-2">
                                    <div>
                                        <div>
                                            {{ $item->item->name ?? 'Item' }} × {{ $item->quantity }}
                                            @if ($item->variant_id)
                                                <span class="text-xs text-red-500">
                                                    ({{ $item->variant->name ?? 'Variant' }})
                                                </span>
                                            @endif
                                        </div>

                                        @if ($item->special_notes)
                                            <div class="text-xs text-red-500 italic">Note: {{ $item->special_notes }}
                                            </div>
                                        @endif

                                    </div>
                                     <div class="flex gap-1">
                                    @if ($item->status === 'pending')
                                        <button wire:click="updateKotItemStatus({{ $item->id }})"
                                            class="px-2 py-0.5 bg-blue-500 text-white rounded text-xs hover:bg-blue-600">
                                            Preparing
                                        </button>
                                        <button wire:click="showCancelItemModal({{ $item->id }})"
                                            class="px-2 py-0.5 bg-red-500 text-white rounded text-xs hover:bg-red-600">
                                            Cancel
                                        </button>
                                    @elseif ($item->status === 'preparing')
                                        <button wire:click="updateKotItemStatus({{ $item->id }})"
                                            class="px-2 py-0.5 bg-green-500 text-white rounded text-xs hover:bg-green-600">
                                            Ready
                                        </button>
                                        <button wire:click="showCancelItemModal({{ $item->id }})"
                                            class="px-2 py-0.5 bg-red-500 text-white rounded text-xs hover:bg-red-600">
                                            Cancel
                                        </button>
                                    @endif
                                </div>
                                </li>
                            @endforeach
                        </ul>


                        <p class="text-xs text-gray-500 mt-1">
                            Printed:
                            {{ $kot->printed_at ? \Carbon\Carbon::parse($kot->printed_at)->format('h:i A') : '-' }}
                        </p>

                        @php
                            $itemStatuses = collect($kot->items)->pluck('status')->unique()->toArray();
                        @endphp

                        {{-- Show "Move to Preparing" only if ALL items are pending --}}
                        @if ($kot->status === 'pending' && count($itemStatuses) === 1 && $itemStatuses[0] === 'pending')
                            <button wire:click="updateKotStatus({{ $kot->id }})"
                                class="mt-2 px-3 py-1 bg-blue-500 text-white rounded text-xs hover:bg-blue-600">
                                Move to Preparing
                            </button>
                        @endif

                        {{-- Show "Move to Ready" if at least one item is preparing, and no item is pending --}}
                        @if (in_array('preparing', $itemStatuses) && !in_array('pending', $itemStatuses))
                            <button wire:click="updateKotStatus({{ $kot->id }})"
                                class="mt-2 px-3 py-1 bg-green-500 text-white rounded text-xs hover:bg-green-600">
                                Move to Ready
                            </button>
                        @endif


                    </div>
                @empty
                    <p class="text-sm text-gray-400">No {{ $activeTab }} orders.</p>
                @endforelse
            </div>
        </div>

          @if($showCancelModal)
        <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-lg p-6 max-w-md w-full">
                <h3 class="text-lg font-semibold mb-4">Cancel Item</h3>
                <p class="mb-2">Please provide a reason for cancellation:</p>
                <textarea wire:model="cancelReason" class="w-full border rounded p-2 mb-4" rows="3" placeholder="Reason for cancellation..."></textarea>
                @error('cancelReason') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                
                <div class="flex justify-end gap-2">
                    <button wire:click="$set('showCancelModal', false)" class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                        Cancel
                    </button>
                    <button wire:click="cancelKotItem" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
                        Confirm Cancellation
                    </button>
                </div>
            </div>
        </div>
    @endif
    </div>
