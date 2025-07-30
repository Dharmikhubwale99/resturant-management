<div class="p-4 space-y-6">
    <div>
        <h2 class="text-lg font-bold text-gray-700 mb-2">GSTR REPORTS</h2>
        <div class="flex flex-wrap gap-2">
            <x-form.button>GSTR-1 Report</x-form.button>
        </div>
    </div>
    <div>
        <h2 class="text-lg font-bold text-gray-700 mb-2">TRANSACTION REPORTS</h2>
        <div class="flex flex-wrap gap-2">
            <x-form.button :route="'restaurant.sales-report'" class="bg-blue-600 hover:bg-blue-600 text-white">
                Sale Report
            </x-form.button>

            <x-form.button :route="'restaurant.staffwise-report'">Staff Wise Sale Report</x-form.button>
            <x-form.button>Sale Wise Profit & Loss Statement</x-form.button>
            <x-form.button>Purchase Report</x-form.button>
            <x-form.button>Money In Report</x-form.button>
            <x-form.button>Money Out Report</x-form.button>
            <x-form.button :route="'restaurant.expense-report'">Expense Report</x-form.button>
        </div>
    </div>
    <div>
        <h2 class="text-lg font-bold text-gray-700 mb-2">PARTY REPORTS</h2>
        <div class="flex flex-wrap gap-2">
            <x-form.button>Party Report</x-form.button>
            <x-form.button>Party Details Report</x-form.button>
            <x-form.button>Party Receivable/Payable Report</x-form.button>
        </div>
    </div>
    <div>
        <h2 class="text-lg font-bold text-gray-700 mb-2">ITEM/STOCK REPORTS</h2>
        <div class="flex flex-wrap gap-2">
            <x-form.button>Stock Summary Report</x-form.button>
            <x-form.button>Item Sale Report</x-form.button>
            <x-form.button>Item Sale Payment Report</x-form.button>
            <x-form.button>Item Report</x-form.button>
            <x-form.button>Item Details Report</x-form.button>
        </div>
    </div>
    <div>
        <h2 class="text-lg font-bold text-gray-700 mb-2">BUSINESS REPORTS</h2>
        <div class="flex flex-wrap gap-2">
            <x-form.button>Day Book Report</x-form.button>
            <x-form.button>All Profiles Day Book Report</x-form.button>
            <x-form.button>Cut Off Day Report</x-form.button>
        </div>
    </div>

</div>
