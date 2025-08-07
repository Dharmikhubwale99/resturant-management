<div class="max-w-xl mx-auto p-4 bg-white rounded shadow">
    <h2 class="text-xl font-bold mb-4">Money Out Form</h2>

   <x-form.error  />

    <form wire:submit.prevent="save" class="space-y-4">
        <div>
            <label>Party Name</label>
            <input type="text" wire:model="party_name" class="w-full border px-3 py-2 rounded">
        </div>

        <div>
            <label>Amount</label>
            <input type="number" step="0.01" wire:model="amount" class="w-full border px-3 py-2 rounded">
        </div>

        <div>
            <label>Date</label>
            <input type="date" wire:model="date" class="w-full border px-3 py-2 rounded">
        </div>

        <div>
            <label>Description</label>
            <textarea wire:model="description" class="w-full border px-3 py-2 rounded"></textarea>
        </div>

        <div>
            <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Save</button>
        </div>
    </form>
</div>
