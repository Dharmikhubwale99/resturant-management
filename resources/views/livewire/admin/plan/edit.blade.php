<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="p-6 bg-white rounded shadow max-w-3xl w-full">
        <h2 class="text-2xl font-bold mb-6 text-center">Edit Plan</h2>
        <x-form.error />
        <form wire:submit.prevent="submit" class="space-y-4">

            <x-form.input name="name" label="Name" wireModel="name" required placeholder="Enter name" />

            <x-form.input name="price" label="Price" type="text" wireModel="price" placeholder="Enter price" />

            <x-form.input name="duration_days" label="Duration (Days)" type="text" wireModel="duration_days"
                placeholder="Enter duration (Days)" />

            <x-form.input name="description" label="Description" type="textarea" wireModel="description"
                placeholder="Enter description" />

            <x-form.input label="Images" name="images" type="file" wireModel="images" />
            <div wire:loading wire:target="images" class="flex items-center justify-center mt-2">
                <svg class="animate-spin h-8 w-8 text-blue-500" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                        stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path>
                </svg>
            </div>
            @if ($images)
                <div class="flex gap-2 mt-2">
                    <div class="relative w-20 h-20">
                        <img src="{{ $images->temporaryUrl() }}" alt="Preview"
                            class="w-20 h-20 object-cover rounded" />
                    </div>
                </div>
            @endif
            @php
                $existingImages = $plan->getMedia('planImages');
            @endphp

            @foreach ($existingImages as $media)
                <div class="flex items-center gap-4 mb-4">
                    <img src="{{ $media->getUrl() }}" class="w-20 h-20 object-cover rounded">
                    <button wire:click.prevent="removeImage({{ $media->id }})"
                        class="bg-red-600 text-white px-4 py-1 rounded-full text-xs hover:bg-red-700">
                        Delete
                    </button>
                </div>
            @endforeach

            <x-form.select name="type" label="Discount Type" wireModelLive="type" :options="['fixed' => 'Fixed', 'percentage' => 'Percentage']" />

            @if ($type === 'percentage')
                <x-form.input name="value" label="Discount Value (%)" wireModel="value" type="number" step="0.01"
                    placeholder="e.g. 10" />
            @endif

            @if ($type === 'fixed')
                <x-form.input name="amount" label="Amount" wireModel="amount" type="number" step="0.01" />
            @endif

            <div class="flex flex-row text-center  space-x-3">
                <x-form.button type="submit" title="Save" wireTarget="submit" />
                <x-form.button title="Back" class="bg-gray-500 hover:bg-gray-600 text-white"
                    route="superadmin.plans.index" />
            </div>
        </form>
    </div>
</div>
