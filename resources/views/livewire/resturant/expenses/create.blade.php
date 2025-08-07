<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="p-6 bg-white rounded shadow max-w-3xl w-full">
        <h2 class="text-2xl font-bold mb-6 text-center">Add Expense</h2>
        <x-form.error />
        <form wire:submit.prevent="submit" class="space-y-4" enctype="multipart/form-data">

            @if (setting('expensetype'))
            <x-form.select name="expense_type_id" label="Expense Type" wireModel="expense_type_id" required :options="$expenseTypes" />
            @endif

            <x-form.select 
                name="name" 
                label="Party Name" 
                wireModel="name" 
                required 
                :options="$partyOptions" 
            />


            <x-form.input name="amount" label="Amount" wireModel="amount" required placeholder="Enter amount"
                type="number" step="0.01" />
                
            <x-form.input name="paid_at" label="Paid At" wireModel="paid_at" type="date" />

            <x-form.input name="description" label="Description" type="textarea" wireModel="description"
                placeholder="Enter description" />

            <div class="flex flex-row text-center  space-x-3">
                <x-form.button type="submit" title="Save" wireTarget="submit" />
                <x-form.button title="Back" class="bg-gray-500 hover:bg-gray-600 text-white"
                    route="restaurant.expenses.index" />
            </div>
        </form>
    </div>
</div>
