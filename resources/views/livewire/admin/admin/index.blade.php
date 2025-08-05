<div class="p-6 bg-white rounded shadow">
    <div class="flex justify-between items-center mb-4">

        <h2 class="text-xl font-bold">Admin List</h2>

        <div class="flex space-x-2">
            <x-form.button title="+ Add" route="superadmin.admin.create"
                class="bg-blue-600 hover:bg-blue-700 text-white" />
        </div>
    </div>
    <x-form.error />
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">#</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Name</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Mobile</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Role</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Created At</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Plan Expiry Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($users as $index => $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 text-sm text-gray-900">{{ $index + 1 }}</td>
                        <td class="px-6 text-sm text-gray-900">{{ $user->name }}</td>
                        <td class="px-6 text-sm text-gray-900">{{ $user->mobile }}</td>
                        <td class="px-6 text-sm text-gray-900">{{ $user->role }}</td>
                        
                        <td class="px-6 text-sm text-gray-900">
                            {{ $user->restaurant_created_at ? \Carbon\Carbon::parse($user->restaurant_created_at)->format('d-m-Y') : '' }}
                        </td>

                        <td class="px-6 text-sm text-gray-900">
                            {{ $user->plan_expiry_at ? \Carbon\Carbon::parse($user->plan_expiry_at)->format('d-m-Y') : '' }}
                        </td>

                        <td class="px-6 text-sm text-gray-900">
                            <div class="flex flex-row items-center space-x-3">
                                <x-form.button title=""
                                    class="p-1 w-5 h-10 rounded flex items-center justify-center mt-3"
                                    :route="['superadmin.admin.edit', ['id' => $user->id]]">
                                    <span class="w-5 h-1 flex items-center justify-center">
                                        {!! file_get_contents(public_path('icon/edit.svg')) !!}
                                    </span>
                                </x-form.button>

                                <x-form.button title=""
                                    class="p-1 w-15 h-10 rounded-full flex items-center justify-center hover:bg-gray-100 transition mt-4"
                                    :route="['superadmin.admin.access', ['id' => $user->id]]">
                                    <img src="{{ asset('icon/access.png') }}" alt="Access"
                                        class="w-6 h-6 object-contain" />
                                </x-form.button>

                                <x-form.button title=""
                                    class="p-1 w-5 h-10 rounded flex items-center justify-center mt-3"
                                    wire:click="confirmDelete({{ $user->id }})">
                                    <span class="w-5 h-1 flex items-center justify-center">
                                        {!! file_get_contents(public_path('icon/delete.svg')) !!}
                                    </span>
                                </x-form.button>

                            </div>

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <div class="mt-4">
            {{ $users->links() }}
        </div>

        <div class="mt-4">

            @if ($confirmingDelete)
            <div class="fixed inset-0 bg-transparent bg-opacity-0 z-40 flex items-center justify-center">
                <div class="bg-white rounded-lg p-6 shadow-xl z-50 w-full max-w-md">
                    <h3 class="text-lg font-semibold mb-4 text-red-600">Confirm Delete</h3>
                    <p class="text-gray-700 mb-6">Are you sure you want to delete this lead? This action cannot be
                        undone.</p>

                    <div class="flex justify-end space-x-3">
                        <button wire:click="cancelDelete"
                            class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-gray-700">Cancel</button>
                        <button wire:click="deleteUser"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Delete</button>
                    </div>
                </div>
            </div>
        @endif
        </div>
    </div>
