@props(['name', 'groups' => [], 'selected' => [], 'labelKey' => 'name', 'valueKey' => 'name', 'wireModel' => null])


<div class="space-y-4" id="permission-wrapper">
    <div class="mb-4">
        <label class="inline-flex items-center space-x-2">
            <input type="checkbox" id="global-select-all">
            <span class="text-sm font-semibold text-blue-600">Select All Permissions</span>
        </label>
    </div>

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
                        <input type="checkbox"
                            class="group-item text-blue-600 border-gray-300 rounded shadow-sm focus:ring-blue-500"
                            value="{{ is_array($item) ? $item[$valueKey] : $item }}"
                            {{ in_array(is_array($item) ? $item[$valueKey] : $item, $selected) ? 'checked' : '' }}>


                        <span>{{ is_array($item) ? $item[$labelKey] : $item }}</span>
                    </label>
                @endforeach
            </div>
        </div>
    @endforeach

    <input type="hidden" id="permissions-hidden" wire:model.defer="{{ $wireModel }}">
</div>

@push('scripts')
    <script>
        function initSelectAllLogic() {
            const globalSelectAll = document.getElementById('global-select-all');
            const hiddenField = document.getElementById('permissions-hidden');

            function syncHiddenField() {
                const selectedValues = Array.from(document.querySelectorAll('.group-item:checked')).map(cb => cb.value);
                hiddenField.value = selectedValues;
                hiddenField.dispatchEvent(new Event('input'));
            }

            globalSelectAll.addEventListener('change', function() {
                const allItems = document.querySelectorAll('.group-item');
                const allGroupToggles = document.querySelectorAll('.select-all');
                allItems.forEach(cb => cb.checked = this.checked);
                allGroupToggles.forEach(cb => cb.checked = this.checked);
                syncHiddenField();
            });

            document.querySelectorAll('.select-all').forEach(groupToggle => {
                groupToggle.addEventListener('change', function() {
                    const groupName = this.dataset.group;
                    const groupItems = document.querySelectorAll(
                        `.group-checkboxes[data-group="${groupName}"] .group-item`);
                    groupItems.forEach(cb => cb.checked = this.checked);
                    syncHiddenField();
                    updateGlobalState();
                });
            });

            document.querySelectorAll('.group-item').forEach(item => {
                item.addEventListener('change', function() {
                    const groupName = this.closest('.group-checkboxes').dataset.group;
                    updateGroupState(groupName);
                    updateGlobalState();
                    syncHiddenField();
                });
            });

            function updateGroupState(groupName) {
                const groupItems = document.querySelectorAll(`.group-checkboxes[data-group="${groupName}"] .group-item`);
                const groupToggle = document.querySelector(`.select-all[data-group="${groupName}"]`);
                const checkedCount = document.querySelectorAll(
                    `.group-checkboxes[data-group="${groupName}"] .group-item:checked`).length;

                groupToggle.checked = (checkedCount === groupItems.length);
                groupToggle.indeterminate = (checkedCount > 0 && checkedCount < groupItems.length);
            }

            function updateGlobalState() {
                const allItems = document.querySelectorAll('.group-item');
                const checkedItems = document.querySelectorAll('.group-item:checked');
                globalSelectAll.checked = (checkedItems.length === allItems.length);
                globalSelectAll.indeterminate = (checkedItems.length > 0 && checkedItems.length < allItems.length);
            }

            syncHiddenField(); // On load
        }

        document.addEventListener('DOMContentLoaded', initSelectAllLogic);
        document.addEventListener('livewire:update', initSelectAllLogic);
    </script>
@endpush
