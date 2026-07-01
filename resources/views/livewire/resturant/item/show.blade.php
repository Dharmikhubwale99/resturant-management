<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-2xl bg-white p-6 rounded shadow w-full">
        <h2 class="text-2xl font-bold mb-4">{{ $item->name }}</h2>
        <div class="mb-2"><strong>Category:</strong> {{ $item->category->name ?? '-' }}</div>
        <div class="mb-2"><strong>Item Type:</strong> {{ ucfirst($item->item_type) }}</div>
        <div class="mb-2"><strong>Short Name:</strong> {{ $item->short_name }}</div>
        <div class="mb-2"><strong>Code:</strong> {{ $item->code }}</div>
        <div class="mb-2"><strong>Price:</strong> ₹{{ $item->price }}</div>
        <div class="mb-2"><strong>Description:</strong> {{ $item->description }}</div>

        <div class="mb-4">
            <strong>Images:</strong>
            <div class="flex gap-2 mt-2">
                    <img src="{{ $item->image_url }}" alt="Image" class="w-20 h-20 object-cover rounded" />
            </div>
        </div>

        <div>
            <strong>Variants:</strong>
            <table class="w-full mt-2 border">
                <thead>
                    <tr>
                        <th class="border px-2 py-1">Name</th>
                        <th class="border px-2 py-1">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($item->variants as $variant)
                        <tr>
                            <td class="border px-2 py-1">{{ $variant->name }}</td>
                            <td class="border px-2 py-1">₹{{ $variant->price }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center py-2">No variants</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            <strong>Addons:</strong>
            <table class="w-full mt-2 border">
                <thead>
                    <tr>
                        <th class="border px-2 py-1">Name</th>
                        <th class="border px-2 py-1">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($item->addons as $addon)
                        <tr>
                            <td class="border px-2 py-1">{{ $addon->name }}</td>
                            <td class="border px-2 py-1">₹{{ $addon->price }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="text-center py-2">No addons</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex justify-end mt-4">
            <x-form.button title="Back" class="bg-gray-500 hover:bg-gray-600 text-white"
                route="restaurant.items.index" />
        </div>
    </div>
</div>
