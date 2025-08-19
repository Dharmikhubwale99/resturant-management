<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="p-6 bg-white rounded shadow max-w-3xl w-full">
        <h2 class="text-2xl font-bold mb-6 text-center">Edit Plan</h2>
        <x-form.error />
        <form wire:submit.prevent="submit" class="space-y-4">

            <x-form.input name="name" label="Name" wireModel="name" required placeholder="Enter name" />

            <x-form.input name="price" label="Price" type="number" wireModel="price" placeholder="Enter price" />

            <x-form.input name="duration_days" label="Duration (Days)" type="number" wireModel="duration_days"
                placeholder="Enter duration (Days)" />

            <x-form.input name="description" label="Description" type="textarea" wireModel="description"
                placeholder="Enter description" />

            <x-form.input name="storage_quota_mb" label="Storage Quota (MB)" type="number" wireModel="storage_quota_mb" placeholder="e.g. 10240 for 10 GB" />

            <x-form.input name="max_file_size_kb" label="Max File Size (KB)" type="number" wireModel="max_file_size_kb" placeholder="e.g. 2048 for 2 MB" />

            <x-form.select name="type" label="Discount Type" wireModelLive="type" :options="['fixed' => 'Fixed', 'percentage' => 'Percentage']" />

            @if ($type === 'percentage')
                <x-form.input name="value" label="Discount Value (%)" wireModel="value" type="number" step="0.01"
                    placeholder="e.g. 10" />
            @endif

            @if ($type === 'fixed')
                <x-form.input name="amount" label="Amount" wireModel="amount" type="number" step="0.01" />
            @endif

            <div class="mb-2">
                <label class="inline-flex items-center">
                    <input type="checkbox" wire:model.live="selectAllFeatures" class="form-checkbox">
                    <span class="ml-2 font-semibold">Select All Features</span>
                </label>
            </div>


            <div class="grid grid-cols-2 gap-2">
                @foreach ($availableFeatures as $feature)
                    <label class="inline-flex items-center">
                        <input type="checkbox" wire:model="featureAccess" value="{{ $feature }}"
                            class="form-checkbox">
                        <span class="ml-2 capitalize">{{ str_replace('_', ' ', $feature) }}</span>
                    </label>
                @endforeach
            </div>

            <div class="flex flex-row text-center  space-x-3">
                <x-form.button type="submit" title="Save" wireTarget="submit" />
                <x-form.button title="Back" class="bg-gray-500 hover:bg-gray-600 text-white"
                    route="superadmin.plans.index" />
            </div>
        </form>
    </div>
</div>
