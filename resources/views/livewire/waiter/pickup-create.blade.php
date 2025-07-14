<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="p-6 bg-white rounded shadow max-w-3xl w-full">
        <h2 class="text-2xl font-bold mb-6 text-center">Add Pickup</h2>
        <x-form.error />
        <form wire:submit.prevent="submit" class="space-y-4">

            <x-form.input name="customer_name" label="Name" wireModel="customer_name" required placeholder="Enter name" />
            <x-form.input name="mobile" label="Phone" wireModel="mobile" required placeholder="Enter phone number" />

            <div class="flex flex-row text-center  space-x-3">
                <x-form.button type="submit" title="Save" wireTarget="submit" />
               
            </div>
        </form>
    </div>
</div>
