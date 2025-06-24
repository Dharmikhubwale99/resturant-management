<div class="p-6 bg-white rounded shadow">
    <div class="flex justify-between items-center mb-4">

        <h2 class="text-xl font-bold">Admin List</h2>
        @if (session()->has('message'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4"
                role="alert">
                <strong class="font-bold">Success!</strong>
                <span class="block sm:inline">{{ session('message') }}</span>
            </div>
        @endif
        @if (session()->has('error'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 3000)" x-show="show"
                class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                <strong class="font-bold">Error!</strong>
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <div class="flex space-x-2">
            <x-form.button title="+ Create" route="superadmin.admin.create"
                class="bg-blue-600 hover:bg-blue-700 text-white" />
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">#</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Mobile</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Role</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($users as $index => $lead)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 text-sm text-gray-900">{{ $index + 1 }}</td>
                        <td class="px-6 text-sm text-gray-900">{{ $lead->name }}</td>
                        <td class="px-6 text-sm text-gray-900">{{ $lead->mobile }}</td>
                        <td class="px-6 text-sm text-gray-900">{{ $lead->role }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">
            {{ $users->links() }}
        </div>

        {{-- @if ($confirmingDelete)
            <div class="fixed inset-0 bg-transparent bg-opacity-0 z-40 flex items-center justify-center">
                <div class="bg-white rounded-lg p-6 shadow-xl z-50 w-full max-w-md">
                    <h3 class="text-lg font-semibold mb-4 text-red-600">Confirm Delete</h3>
                    <p class="text-gray-700 mb-6">Are you sure you want to delete this lead? This action cannot be
                        undone.</p>

                    <div class="flex justify-end space-x-3">
                        <button wire:click="cancelDelete"
                            class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-gray-700">Cancel</button>
                        <button wire:click="deleteLead"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Delete</button>
                    </div>
                </div>
            </div>
        @endif --}}
    </div>
</div>
