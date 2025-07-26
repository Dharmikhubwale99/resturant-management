@props(['name', 'groups' => [], 'labelKey' => 'name', 'valueKey' => 'name', 'wireModel' => null])

<div class="space-y-4">
    @foreach ($groups as $groupName => $items)
        <div class="border border-gray-200 rounded p-3">
            <div class="flex items-center justify-between mb-2">
                <label class="inline-flex items-center space-x-2">
                    <input type="checkbox" class="select-all" data-group="{{ $groupName }}">
                    <span class="text-sm font-semibold text-gray-600">{{ ucfirst($groupName) }}</span>
                </label>
            </div>

            <div class="grid grid-cols-3 gap-2 group-checkboxes" data-group="{{ $groupName }}">
                @foreach ($items as $item)
                    <label class="inline-flex items-center space-x-2 text-sm text-gray-700">
                        <input type="checkbox" name="{{ $name }}[]"
                            value="{{ is_array($item) ? $item[$valueKey] : $item }}" wire:model.defer="permissions"
                            class="text-blue-600 border-gray-300 rounded shadow-sm focus:ring-blue-500">
                        <span>{{ is_array($item) ? $item[$labelKey] : $item }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.select-all').forEach(selectAllCheckbox => {
                selectAllCheckbox.addEventListener('change', function() {
                    let groupName = this.dataset.group;
                    let checkboxes = document.querySelectorAll('.group-checkboxes[data-group="' +
                        groupName + '"] input[type="checkbox"]');
                    checkboxes.forEach(cb => {
                        cb.checked = this.checked;
                        cb.dispatchEvent(new Event('input'));
                    });
                });
            });
        });

        document.querySelectorAll('.select-all').forEach(selectAllCheckbox => {
            selectAllCheckbox.addEventListener('change', function() {
                let groupName = this.dataset.group;
                let checkboxes = document.querySelectorAll(
                    '.group-checkboxes[data-group="' + groupName + '"] input[type="checkbox"]'
                );
                checkboxes.forEach(cb => {
                    cb.checked = this.checked;
                    cb.dispatchEvent(new Event('change'));
                });
            });
        });
    </script>
@endpush
