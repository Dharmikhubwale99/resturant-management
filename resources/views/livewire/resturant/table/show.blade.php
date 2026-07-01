@php
    use SimpleSoftwareIO\QrCode\Facades\QrCode;
@endphp

<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-xl bg-white p-6 rounded shadow w-full">
        <h2 class="text-2xl font-bold mb-4">{{ $table->name }}</h2>
        <div class="mb-2"><strong>Area:</strong> {{ $table->area->name ?? '-' }}</div>
        <div class="mb-2"><strong>Capacity:</strong> {{ $table->capacity }}</div>
        <div class="mb-2"><strong>Status:</strong> {{ ucfirst($table->status) }}</div>
        <div class="mb-2"><strong>QR Enabled:</strong> {{ $table->qr_enabled ? 'Yes' : 'No' }}</div>
        <div class="mb-2"><strong>QR Token:</strong> {{ $table->qr_token ?? '-' }}</div>

        <div class="mb-4">
            <strong>QR Code:</strong>
            <div class="mt-2">
                {!! QrCode::size(180)->generate('https://hubwale.com/') !!}

            </div>
            <div class="text-xs text-gray-500 mt-1">
                Scan to open menu for this table
            </div>
        </div>

        <div class="flex flex-row text-center space-x-3 mt-4">
            <a href="{{ route('restaurant.tables.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded">Back</a>
        </div>
    </div>
</div> 