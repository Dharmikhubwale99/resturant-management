<div class="p-4 sm:p-6">
    <h2 class="text-xl sm:text-2xl font-bold mb-4">Plan Report</h2>

    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-6 mb-4 sm:mb-6">
        <div class="bg-white shadow rounded p-2 sm:p-4">
            <p class="text-gray-500 text-xs sm:text-sm">Total Restaurants</p>
            <p class="text-lg sm:text-2xl font-semibold">{{ $totalRestaurants }}</p>
        </div>
        <div class="bg-white shadow rounded p-2 sm:p-4">
            <p class="text-gray-500 text-xs sm:text-sm">Free Trial Users</p>
            <p class="text-lg sm:text-2xl font-semibold">{{ $freeTrialCount }}</p>
        </div>
        <div class="bg-white shadow rounded p-2 sm:p-4">
            <p class="text-gray-500 text-xs sm:text-sm">Filtered Income</p>
            <p class="text-lg sm:text-2xl font-semibold">&#8377; {{ number_format($totalIncome, 2) }}</p>
        </div>
        <div class="bg-white shadow rounded p-2 sm:p-4">
            <p class="text-gray-500 text-xs sm:text-sm">Current Count</p>
            <p class="text-lg sm:text-2xl font-semibold">{{ $purchases->count() }}</p>
        </div>
    </div>

    <div class="bg-white shadow rounded p-3 sm:p-4 mb-4">
        <div class="flex flex-col gap-3 sm:gap-4 mb-3 sm:mb-4">
            <div class="flex justify-between items-center">
                <h3 class="text-base sm:text-lg font-semibold">Purchases</h3>
                <button wire:click="export" wire:loading.attr="disabled" class="bg-green-500 text-white px-2 py-1 sm:px-3 sm:py-1 rounded text-xs sm:text-sm flex items-center gap-1">
                    <svg wire:loading wire:target="export" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                    </svg>
                    <span class="hidden sm:inline">Export</span>
                </button>
            </div>
            
            <div class="grid grid-cols-1 sm:flex sm:flex-row gap-2">
                <select wire:model.live="planType" class="border rounded px-2 py-1 sm:px-3 sm:py-1 text-xs sm:text-sm">
                    <option value="all">All Plans</option>
                    <option value="free">Free Only</option>
                    <option value="paid">Paid Only</option>
                </select>
                
                <select wire:model.live="filterPeriod" class="border rounded px-2 py-1 sm:px-3 sm:py-1 text-xs sm:text-sm">
                    <option value="today">Today</option>
                    <option value="monthly">Month</option>
                    <option value="custom">Custom</option>
                    <option value="all">All</option>
                </select>
            </div>
            
            @if($showCustomRange)
                <div class="grid grid-cols-2 sm:flex sm:flex-row gap-2">
                    <input type="date" wire:model="startDate" class="border rounded px-2 py-1 text-xs sm:text-sm">
                    <input type="date" wire:model="endDate" class="border rounded px-2 py-1 text-xs sm:text-sm">
                    <button wire:click="applyCustomRange" class="col-span-2 sm:col-span-1 bg-blue-500 text-white px-2 py-1 rounded text-xs sm:text-sm">
                        Apply Dates
                    </button>
                </div>
            @endif
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 text-xs sm:text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-2 py-1 sm:px-4 sm:py-2 text-left font-medium text-gray-500 uppercase">#</th>
                        <th class="px-2 py-1 sm:px-4 sm:py-2 text-left font-medium text-gray-500 uppercase">Name</th>
                        <th class="px-2 py-1 sm:px-4 sm:py-2 text-left font-medium text-gray-500 uppercase">Plan</th>
                        <th class="px-2 py-1 sm:px-4 sm:py-2 text-left font-medium text-gray-500 uppercase">Price</th>
                        <th class="px-2 py-1 sm:px-4 sm:py-2 text-left font-medium text-gray-500 uppercase">Mobile</th>
                        <th class="px-2 py-1 sm:px-4 sm:py-2 text-left font-medium text-gray-500 uppercase">Created</th>
                        <th class="px-2 py-1 sm:px-4 sm:py-2 text-left font-medium text-gray-500 uppercase">Expiry</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($purchases as $index => $resto)
                        <tr>
                            <td class="px-2 py-1 sm:px-4 sm:py-2 whitespace-nowrap">{{ $index + 1 }}</td>
                            <td class="px-2 py-1 sm:px-4 sm:py-2 whitespace-nowrap">{{ $resto->name }}</td>
                            <td class="px-2 py-1 sm:px-4 sm:py-2 whitespace-nowrap">{{ $resto->plan->name ?? '-' }}</td>
                            <td class="px-2 py-1 sm:px-4 sm:py-2 whitespace-nowrap">&#8377;{{ $resto->plan->price ?? 0 }}</td>
                            <td class="px-2 py-1 sm:px-4 sm:py-2 whitespace-nowrap">{{ $resto->mobile }}</td>
                            <td class="px-2 py-1 sm:px-4 sm:py-2 whitespace-nowrap">{{ $resto->created_at ? \Carbon\Carbon::parse($resto->created_at)->format('d-m-Y') : '' }}</td>
                            <td class="px-2 py-1 sm:px-4 sm:py-2 whitespace-nowrap">{{ $resto->plan_expiry_at ? \Carbon\Carbon::parse($resto->plan_expiry_at)->format('d-m-Y') : '' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-2 text-center text-gray-500">No purchases found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>