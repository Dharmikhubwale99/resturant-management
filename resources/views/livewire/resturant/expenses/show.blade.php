@php
    use SimpleSoftwareIO\QrCode\Facades\QrCode;
@endphp

<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-xl bg-white p-6 rounded shadow w-full">
        <h2 class="text-2xl font-bold mb-4">{{ $expense->name }}</h2>
        <div class="mb-2"><strong>Expense:</strong> {{ $expense->expenseType->name ?? '-' }}</div>
        <div class="mb-2"><strong>Amount:</strong> {{ $expense->amount }}</div>
        <div class="mb-2"><strong>Description:</strong> {{ $expense->description }}</div>

        <div class="flex flex-row text-center space-x-3 mt-4">
            <a href="{{ route('restaurant.expenses.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Back</a>
        </div>
    </div>
</div> 