<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-xl bg-white p-6 rounded shadow w-full">
        <h2 class="text-2xl font-bold mb-6 text-center">Edit Table</h2>
        <x-form.error />
        <form wire:submit.prevent="submit" class="space-y-4">
            @if (setting('area_module'))
            <x-form.select name="area_id" label="Area" wire:model="area_id" :options="$areas" required />
            @endif
            <x-form.input name="name" label="Table Name" wire:model="name" required />
            <x-form.input name="capacity" label="Capacity" wire:model="capacity" type="number" required />
            {{-- <x-form.select name="status" label="Status" wire:model="status" :options="['available'=>'Available','occupied'=>'Occupied','reserved'=>'Reserved']" required /> --}}
            <div class="flex items-center">
                <input type="checkbox" wire:model="qr_enabled" id="qr_enabled" class="mr-2" />
                <label for="qr_enabled">QR Enabled</label>
            </div>
            <div class="flex flex-row text-center space-x-3">
                <x-form.button type="submit" title="Save" wireTarget="submit" />
                <x-form.button title="Back" class="bg-gray-500 hover:bg-gray-600 text-white"
                    route="restaurant.tables.index" />
            </div>
        </form>
    </div>
</div>
