<div class="p-6 bg-white rounded shadow">
    <div class="mb-4">
        <div class="flex flex-col gap-3 md:gap-4">
            <div class="flex items-center justify-between min-w-0">
                <h1 class="text-xl font-bold">Party List</h1>

                <div class="shrink-0 md:ml-4 flex flex-row gap-3">
                    @can('party-create')
                        @if (setting('party'))
                            <x-form.button title="+ Add" class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-2"
                                route="restaurant.party.create" />
                        @endif
                    @endcan
                </div>
            </div>

            <div class="flex flex-row justify-end sm:flex-row sm:items-center w-full">
                <x-form.input name="search" placeholder="Search by name mobile" wireModelLive="search"
                    wrapperClass="mb-0 w-full md:w-72"
                    inputClass="w-full border border-gray-300 focus:ring focus:ring-blue-300" />
            </div>

        </div>
    </div>

    @if ($parties->count())
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">#</th>
                        <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                        <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Mobile
                        </th>
                        <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Created At
                        </th>
                        <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Status
                        </th>
                        <th class="px-6 whitespace-nowrap py-3 text-left text-sm font-semibold text-gray-700">Action
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach ($parties as $index => $party)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 whitespace-nowrap py-3 text-sm text-gray-900">{{ $index + 1 }}</td>
                            <td class="px-6 whitespace-nowrap py-3 text-sm text-gray-900">{{ $party->name ?? '—' }}</td>
                            <td class="px-6 whitespace-nowrap py-3 text-sm text-gray-900">{{ $party->mobile ?? '—' }}
                            </td>
                            <td class="px-6 whitespace-nowrap py-3 text-sm text-gray-900">
                                {{ $party->created_at?->format('d-m-Y H:i') ?? '—' }}</td>
                            <td class="px-6 whitespace-nowrap text-sm">
                                @if ($party->is_active)
                                    <span class="bg-red-100 text-red-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                        Inactive
                                    </span>
                                @else
                                    <span
                                        class="bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded">
                                        Active
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 text-sm text-gray-900">
                                <div class="flex items-center justify-start space-x-2">
                                    <x-form.button title=""
                                        class=" p-1 w-5 h-10 rounded flex items-center justify-center mt-3"
                                        wireClick="confirmBlock({{ $party->id }})">
                                        @if ($party->is_active)
                                            <span class="w-5 h-1 flex items-center justify-center">
                                                {!! file_get_contents(public_path('icon/xmark.svg')) !!} </span>
                                        @else
                                            <span class="w-5 h-1 flex items-center justify-center">
                                                {!! file_get_contents(public_path('icon/check.svg')) !!} </span>
                                        @endif
                                    </x-form.button>

                                    <x-form.button title=""
                                        class="w-8 h-8 rounded flex items-center justify-center" :route="['restaurant.party.edit', $party->id]">
                                        <span class="w-4 h-4">
                                            {!! file_get_contents(public_path('icon/edit.svg')) !!}
                                        </span>
                                    </x-form.button>

                                    <x-form.button title=""
                                        class="w-8 h-8 rounded flex items-center justify-center"
                                        wireClick="confirmDelete({{ $party->id }})">
                                        <span class="w-4 h-4">
                                            {!! file_get_contents(public_path('icon/delete.svg')) !!}
                                        </span>
                                    </x-form.button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-gray-600">No customers found.</p>
        </div>
    @endif

    @if ($confirmingDelete)
        <div class="fixed inset-0 bg-transparent bg-opacity-0 z-40 flex items-center justify-center">
            <div class="bg-white rounded-lg p-6 shadow-xl z-50 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4 text-red-600">Confirm Delete</h3>
                <p class="text-gray-700 mb-6">Are you sure you want to delete this party? This action cannot be
                    undone.</p>

                <div class="flex justify-end space-x-3">
                    <button wire:click="cancelDelete"
                        class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-gray-700">Cancel</button>
                    <button wire:click="deleteParty({{ $party->id }})"
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Delete</button>
                </div>
            </div>
        </div>
    @endif

    @if ($confirmingBlock)
        <div class="fixed inset-0 bg-transparent bg-opacity-0 z-40 flex items-center justify-center">
            <div class="bg-white rounded-lg p-6 shadow-xl z-50 w-full max-w-md">
                <h3 class="text-lg font-semibold mb-4 text-yellow-600">
                    {{ optional(\App\Models\Customer::find($partyId))->is_active ? 'Confirm Unblock' : 'Confirm Block' }}
                </h3>
                <p class="text-gray-700 mb-6">
                    Are you sure you want to
                    {{ optional(\App\Models\Customer::find($partyId))->is_active ? 'unblock' : 'block' }}
                    this party?
                </p>

                <div class="flex justify-end space-x-3">
                    <button wire:click="cancelBlock"
                        class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-gray-700">Cancel</button>
                    <button wire:click="toggleBlock"
                        class="px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-700">
                        {{ optional(\App\Models\Customer::find($partyId))->is_active ? 'UnBlock' : 'Block' }}
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
