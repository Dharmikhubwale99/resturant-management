<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="p-6 bg-white rounded shadow max-w-3xl w-full">
        <h2 class="text-2xl font-bold mb-6 text-center">Add Expense Type</h2>
        <x-form.error />
        <form wire:submit.prevent="submit" class="space-y-4">

            <x-form.input name="name" label="Name" wireModel="name" required placeholder="Enter name" />

            <div class="flex flex-row text-center  space-x-3">
                <x-form.button type="submit" title="Save" wireClick="submit" wireTarget="submit" />
                <x-form.button title="Back" class="bg-gray-500 hover:bg-gray-600 text-white"
                    route="restaurant.expense-types.index" />
            </div>
        </form>
    </div>
</div>

