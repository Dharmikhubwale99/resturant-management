@props([
    'name',
    'groups' => [],
    'labelKey' => 'name',
    'valueKey' => 'name',
    'wireModel' => null,
])

<div class="space-y-4">
    @foreach ($groups as $groupName => $items)
        <div class="border border-gray-200 rounded p-3">
            <p class="text-sm font-semibold text-gray-600 mb-2">{{ ucfirst($groupName) }}</p>
            <div class="grid grid-cols-2 gap-2">
                @foreach ($items as $item)
                    <label class="inline-flex items-center space-x-2 text-sm text-gray-700">
                        <input type="checkbox" name="{{ $name }}[]"
                            value="{{ is_array($item) ? $item[$valueKey] : $item }}"
                            @if ($wireModel) wire:model="{{ $wireModel }}" @endif
                            class="text-blue-600 border-gray-300 rounded shadow-sm focus:ring-blue-500">
                        <span>{{ is_array($item) ? $item[$labelKey] : $item }}</span>
                    </label>
                @endforeach

            </div>
        </div>
    @endforeach
</div>
