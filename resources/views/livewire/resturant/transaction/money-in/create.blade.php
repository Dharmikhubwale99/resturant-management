<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-xl w-full p-6 bg-white rounded shadow">
        <h1 class="text-2xl font-bold mb-4">Add Payment Entry</h1>

        <form wire:submit.prevent="save" class="space-y-4">
            <x-form.select name="selectedCustomerId" label="Customer" :options="$customerId" wireModel="selectedCustomerId"
                placeholder="Select a customer" required="true" />

            <x-form.select name="method" label="Payment Method" :options="$paymentMethod" wireModel="method"
                placeholder="Select payment method" required="true" />

            <x-form.input name="transactionDate" label="Transaction Date" type="date" wireModel="date"
                required="true" placeholder="Select transaction date" />

            <x-form.input name="amount" label="Amount" type="number" wireModel="amount" required="true"
                placeholder="Enter amount paid" />

            <x-form.button title="Save Payment" type="submit" wireTarget="save" class="bg-green-600 text-white" />
        </form>
    </div>
</div>
