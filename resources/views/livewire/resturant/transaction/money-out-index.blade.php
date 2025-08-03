<div class="p-6 bg-white rounded shadow">
    <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold">Money Out List</h2>

        <div class="flex items-center gap-4">
            <x-form.input name="search" placeholder="Search by party or description"
                wireModelLive="search"
                wrapperClass="mb-0"
                inputClass="w-72 border border-gray-300 focus:ring focus:ring-blue-300" />

            <x-form.input type="date" name="from_date" wireModelLive="from_date"
                wrapperClass="mb-0"
                inputClass="border border-gray-300 focus:ring focus:ring-blue-300" />

            <x-form.input type="date" name="to_date" wireModelLive="to_date"
                wrapperClass="mb-0"
                inputClass="border border-gray-300 focus:ring focus:ring-blue-300" />

            @can('money-out-create')
                <x-form.button title="+ Add" route="restaurant.money-out.create"
                    class="bg-blue-600 hover:bg-blue-700 text-white" />
            @endcan
        </div>
    </div>

    <x-form.error />

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 border border-gray-300">
            <thead class="bg-gray-100">
                <tr>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">#</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Party</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Amount</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Description</th>
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($moneyOuts as $index => $moneyOut)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-3 text-sm text-gray-900">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 text-sm text-gray-900">{{ $moneyOut->party_name ?? '-' }}</td>
                        <td class="px-6 py-3 text-sm text-gray-900">₹{{ number_format($moneyOut->amount, 2) }}</td>
                        <td class="px-6 py-3 text-sm text-gray-900">{{ \Carbon\Carbon::parse($moneyOut->date)->format('d-m-Y') }}</td>
                        <td class="px-6 py-3 text-sm text-gray-900">{{ $moneyOut->description }}</td>
                        <td class="px-6 text-sm text-gray-900">
                            <div class="flex items-center justify-start space-x-2">
                                @can('money-out-edit')
                                    <x-form.button title="" class="w-8 h-8 rounded flex items-center justify-center"
                                        :route="['restaurant.money-out.edit', $moneyOut->id]">
                                        <span class="w-4 h-4">
                                            {!! file_get_contents(public_path('icon/edit.svg')) !!}
                                        </span>
                                    </x-form.button>
                                @endcan
                                @can('money-out-delete')
                                    <x-form.button title="" class="w-8 h-8 rounded flex items-center justify-center"
                                        wireClick="confirmDelete({{ $moneyOut->id }})">
                                        <span class="w-4 h-4">
                                            {!! file_get_contents(public_path('icon/delete.svg')) !!}
                                        </span>
                                    </x-form.button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No entries found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="mt-4">
            {{ $moneyOuts->links() }}
        </div>
    </div>
</div>
