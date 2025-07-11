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
                $kotList = match($activeTab) {
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

                    <ul class="text-sm text-gray-700 mt-1">
                        @foreach ($kot->items as $item)
                            <li>{{ $item->item->name ?? 'Item' }} × {{ $item->quantity }}</li>
                        @endforeach
                    </ul>

                    <p class="text-xs text-gray-500 mt-1">
                        Printed: {{ $kot->printed_at ? \Carbon\Carbon::parse($kot->printed_at)->format('h:i A') : '-' }}
                    </p>
                </div>
            @empty
                <p class="text-sm text-gray-400">No {{ $activeTab }} orders.</p>
            @endforelse
        </div>
    </div>
</div>
