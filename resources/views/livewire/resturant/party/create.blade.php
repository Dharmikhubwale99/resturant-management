<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-xl w-full p-6 bg-white rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Add New Customer</h1>

        <x-form.error />

        <form wire:submit.prevent="save" class="space-y-4">
            <x-form.input name="name" label="Name" required="true" wireModel="name" placeholder="Enter customer name" />

            <x-form.input name="mobile" label="Mobile" wireModel="mobile" placeholder="Enter mobile number"
                maxlength="10" oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)" required="true" />


            <div class="flex gap-3">
                <x-form.button title="Save" type="submit" wireTarget="save" />

                <x-form.button :route="'restaurant.party'" class="bg-gray-500 text-white">
                    Back
                </x-form.button>
            </div>
        </form>
    </div>
</div>
