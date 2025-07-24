<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="p-6 bg-white rounded shadow max-w-3xl w-full">
        <h2 class="text-2xl font-bold mb-6 text-center">Add Plan</h2>
        <x-form.error />
        <form wire:submit.prevent="submit" class="space-y-4">

            <x-form.input name="name" label="Name" wireModel="name" required placeholder="Enter name" />

            <x-form.input name="price" label="Price" type="text" wireModel="price" placeholder="Enter price" />

            <x-form.input name="duration_days" label="Duration (Days)" type="text" wireModel="duration_days"
                placeholder="Enter duration (Days)" />

            <x-form.input name="description" label="Description" type="textarea" wireModel="description"
                placeholder="Enter description" />

            <div class="flex flex-row text-center  space-x-3">
                <x-form.button type="submit" title="Save" wireClick="submit" wireTarget="submit" />
                <x-form.button title="Back" class="bg-gray-500 hover:bg-gray-600 text-white"
                    route="superadmin.plans.index" />
            </div>
        </form>
    </div>
</div>
