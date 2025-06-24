<div class="p-6 bg-white rounded shadow">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Plan List</h2>
        <div class="flex space-x-2">
            <x-form.button title="Add" route="" class="bg-blue-600 hover:bg-blue-700 text-white" />
        </div>
    </div>
    <x-form.error />
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">#</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Price</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($plans as $plan)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 text-sm text-gray-900">{{ $loop->iteration }}</td>
                        <td class="px-6 text-sm text-gray-900">{{ $plan->name }}</td>
                        <td class="px-6 text-sm text-gray-900">{{ $plan->price }}</td>
                        <td class="px-6 text-sm text-gray-900">
                            <x-form.button title=""
                                    class=" p-1 w-5 h-10 rounded flex items-center justify-center mt-3"
                                    wireClick="confirmDelete({{ $plan->id }})">
                                    <span class="w-5 h-1 flex items-center justify-center">
                                        {{-- {!! file_get_contents(public_path('icon/delete.svg')) !!} --}}
                                    </span>
                            </x-form.button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">
            {{ $plans->links() }}
        </div>

        @if ($confirmingDelete)
            <div class="fixed inset-0 bg-transparent bg-opacity-0 z-40 flex items-center justify-center">
                <div class="bg-white rounded-lg p-6 shadow-xl z-50 w-full max-w-md">
                    <h3 class="text-lg font-semibold mb-4 text-red-600">Confirm Delete</h3>
                    <p class="text-gray-700 mb-6">Are you sure you want to delete this plan? This action cannot be
                        undone.</p>

                    <div class="flex justify-end space-x-3">
                        <button wire:click="cancelDelete"
                            class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-gray-700">Cancel</button>
                        <button wire:click="deletePlan"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Delete</button>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
