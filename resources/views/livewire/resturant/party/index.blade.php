<div>
    <div class="p-6">
        <h1 class="text-2xl font-bold mb-4">Party / Customer List</h1>
        <div class="mb-4">
            <a href="{{ route('restaurant.party.create') }}"
               class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                Add New Customer
            </a>
        @if ($parties->count())
            <table class="min-w-full border text-sm">
                <thead class="bg-gray-100">
                    <tr class="text-left">
                        <th class="px-4 py-2 border">#</th>
                        <th class="px-4 py-2 border">Name</th>
                        <th class="px-4 py-2 border">Mobile</th>
                        <th class="px-4 py-2 border">Created At</th>
                        <th class="px-4 py-2 border">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($parties as $index => $party)
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-4 py-2 border">{{ $index + 1 }}</td>
                            <td class="px-4 py-2 border">{{ $party->name ?? '—' }}</td>
                            <td class="px-4 py-2 border">{{ $party->mobile ?? '—' }}</td>
                            <td class="px-4 py-2 border">{{ $party->created_at?->format('d-m-Y H:i') ?? '—' }}</td>
                            <td class="px-4 py-2 border">
                                {{-- <a href="{{ route('resturant.party.view', $party->id) }}"
                                   class="text-blue-600 hover:underline text-sm">View</a> --}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p class="text-gray-600">No customers found.</p>
        @endif
    </div>

</div>
