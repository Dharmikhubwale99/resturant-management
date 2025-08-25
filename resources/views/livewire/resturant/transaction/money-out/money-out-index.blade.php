<div class="p-6 bg-white rounded shadow">
    <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 sm:gap-4 mb-4">
        <h2 class="text-xl font-bold truncate">Money Out List</h2>

        <div class="w-full sm:w-auto flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
            <div class="flex flex-row gap-3">
            <x-form.input type="date" name="from_date" wireModelLive="from_date" wrapperClass="mb-0 w-full sm:w-auto"
                inputClass="w-full sm:w-auto border border-gray-300 focus:ring focus:ring-blue-300" />

            <x-form.input type="date" name="to_date" wireModelLive="to_date" wrapperClass="mb-0 w-full sm:w-auto"
                inputClass="w-full sm:w-auto border border-gray-300 focus:ring focus:ring-blue-300" />

            </div>
            <x-form.input name="search" placeholder="Search by party or description" wireModelLive="search"
                wrapperClass="mb-0 w-full sm:w-auto"
                inputClass="w-full sm:w-72 border border-gray-300 focus:ring focus:ring-blue-300" />

            @can('moneyout-create')
                @if (setting('moneyOut'))
                    <x-form.button title="+ Add" route="restaurant.money-out.create"
                        class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white" />
                @endif
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
                    <th class="px-6 py-3 text-left text-sm font-semibold text-gray-700">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach ($records as $record)
                    <tr>
                        <td class="px-6 py-3 text-sm text-gray-900">{{ $loop->iteration }}</td>
                        <td class="px-6 py-3 text-sm text-gray-900">{{ $record['party_name'] }}</td>
                        <td class="px-6 py-3 text-sm text-gray-900">{{ $record['amount'] }}</td>
                        <td class="px-6 py-3 text-sm text-gray-900">{{ $record['date'] }}</td>
                        <td class="px-6 py-3 text-sm text-gray-900">{{ $record['description'] }}</td>
                        <td class="px-6 py-3 text-sm text-gray-900">
                            <span
                                class="text-xs rounded px-2 py-1 {{ $record['type'] === 'expense' ? 'bg-red-100 text-red-600' : 'bg-blue-100 text-blue-600' }}">
                                {{ ucfirst($record['type']) }}
                            </span>
                        </td>
                    </tr>
                @endforeach
                {{ $records->links() }}
            </tbody>
        </table>
    </div>
</div>
