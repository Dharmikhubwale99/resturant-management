<div class="flex p-4 gap-4">
    <div class="w-1/4 bg-white p-4 rounded shadow">
        <h2 class="text-lg font-bold mb-4">Categories</h2>

        <ul class="space-y-2">
            <li>
                <button wire:click="clearCategory"
                        class="w-full px-3 py-2 text-left rounded {{ $selectedCategory === null ? 'bg-blue-200' : 'hover:bg-blue-100' }}">
                    All
                </button>
            </li>
            @foreach ($categories as $category)
                <li>
                    <button
                        wire:key="cat-{{ $category->id }}"
                        wire:click="selectCategory({{ $category->id }})"
                        class="w-full px-3 py-2 text-left rounded
                            {{ $selectedCategory === $category->id ? 'bg-blue-200' : 'hover:bg-blue-100' }}"
                    >
                        {{ $category->name }}
                    </button>
                </li>
            @endforeach

        </ul>
    </div>

    <div class="flex-1">
        <h2 class="text-xl font-bold mb-4">Items</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @forelse ($filteredItems as $item)
                <div class="bg-white p-4 rounded shadow">
                    <h3 class="text-lg font-semibold">{{ $item->name }}</h3>
                    <p class="text-sm text-gray-600">{{ $item->description }}</p>
                    <p class="text-gray-900 font-bold mt-2">₹{{ number_format($item->price, 2) }}</p>
                </div>
            @empty
                <p class="text-gray-600 col-span-full">No items found in this category.</p>
            @endforelse
        </div>
    </div>
</div>
