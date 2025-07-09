<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="p-6 bg-white rounded shadow max-w-3xl w-full">
        <h2 class="text-2xl font-bold mb-6 text-center">Add Discount</h2>

        <x-form.error />

        <form wire:submit.prevent="submit" class="space-y-4">
            <div>
                <div class="flex items-center gap-3">
                    <div class="flex-1">
                        <x-form.input name="code" label="Coupon Code" wireModel="code" placeholder="Enter coupon code" required />
                    </div>
                    <div class="mt-2">
                        <a wire:click="generateCode" class="text-sm text-blue-600 hover:underline cursor-pointer">Generate</a>
                    </div>
                </div>
            </div>

            <x-form.select name="type" label="Discount Type" wireModelLive="type" required
                :options="['fixed' => 'Fixed', 'percentage' => 'Percentage']" />

            @if ($type === 'percentage')
                <x-form.input name="value" label="Discount Value (%)" wireModel="value" type="number" step="0.01" placeholder="e.g. 10" required />
            @endif

            @if ($type === 'fixed')
                <x-form.input name="minimum_amount" label="Minimum Order Amount" wireModel="minimum_amount" type="number" step="0.01" required />
                <x-form.input name="maximum_discount" label="Maximum Discount Amount" wireModel="maximum_discount" type="number" step="0.01" required />
            @endif

            <x-form.input name="max_uses" label="Max Uses" wireModel="max_uses" type="number" placeholder="Optional" />

            <x-form.input name="starts_at" label="Start Date" wireModel="starts_at" type="datetime-local" required />

            <x-form.input name="ends_at" label="End Date" wireModel="ends_at" type="datetime-local" required />

            <div class="flex flex-row text-center space-x-3">
                <x-form.button type="submit" title="Save" wireClick="submit" wireTarget="submit" />
                <x-form.button title="Back" class="bg-gray-500 hover:bg-gray-600 text-white" route="restaurant.discount.index" />
            </div>
        </form>
    </div>
</div>
