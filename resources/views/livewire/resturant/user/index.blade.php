<div class="p-6 bg-white rounded shadow">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">User List</h2>

        <div class="flex items-center gap-4">
            <x-form.input name="search" placeholder="Search by name, email, mobile or referrer" wireModelLive="search"
                wrapperClass="mb-0" inputClass="w-72 border border-gray-300 focus:ring focus:ring-blue-300" />

            <x-form.select name="role" wireModelLive="role" :options="['all' => 'All', 'manager' => 'Manager', 'waiter' => 'Waiter', 'kitchen' => 'Kitchen']" wrapperClass="mb-0"
                inputClass="w-48 border border-gray-300 focus:ring focus:ring-blue-300" />

            <x-form.button title="+ Add" route="restaurant.users.create"
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
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Email</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Mobile</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Role</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($users as $index => $user)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-sm text-gray-900">{{ $user->id }}</td>
                        <td class="px-6 py-3 text-sm text-gray-900">{{ $user->name }}</td>
                        <td class="px-6 py-3 text-sm text-gray-900">{{ $user->email }}</td>
                        <td class="px-6 py-3 text-sm text-gray-900">{{ $user->mobile }}</td>
                        <td class="px-6 py-3 text-sm text-gray-900">{{ $user->role }}</td>
                        <td class="px-6 text-sm text-gray-900">
                             <div class="flex items-center justify-start space-x-2">
                                    <x-form.button title=""
                                        class="w-8 h-8 rounded flex items-center justify-center" :route="['restaurant.users.edit', $user->id]">
                                        <span class="w-4 h-4">
                                            {!! file_get_contents(public_path('icon/edit.svg')) !!}
                                        </span>
                                    </x-form.button>

                                    <x-form.button title=""
                                        class="w-8 h-8 rounded flex items-center justify-center"
                                        wireClick="confirmDelete({{ $user->id }})">
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
        <div class="mt-4">
            {{ $users->links() }}
        </div>
        @if ($confirmingDelete)
            <div class="fixed inset-0 bg-transparent bg-opacity-0 z-40 flex items-center justify-center">
                <div class="bg-white rounded-lg p-6 shadow-xl z-50 w-full max-w-md">
                    <h3 class="text-lg font-semibold mb-4 text-red-600">Confirm Delete</h3>
                    <p class="text-gray-700 mb-6">Are you sure you want to delete this user? This action cannot be
                        undone.</p>

                    <div class="flex justify-end space-x-3">
                        <button wire:click="cancelDelete"
                            class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-gray-700">Cancel</button>
                        <button wire:click="deleteUser({{ $user->id }})"
                            class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">Delete</button>
                    </div>
                </div>
            </div>
        @endif

    </div>
</div>
