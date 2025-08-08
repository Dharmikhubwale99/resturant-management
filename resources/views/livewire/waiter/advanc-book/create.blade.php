<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="p-6 bg-white rounded shadow max-w-3xl w-full">
        <h2 class="text-2xl font-bold mb-6 text-center">Advance Booking</h2>
    <x-form.error />
    <form wire:submit.prevent="saveBooking">
        <div class="mb-4">
            <label for="selectedTable">Select Table:</label>
            <select wire:model="selectedTable" id="selectedTable" class="w-full border rounded p-2">
                <option value="">-- Choose Table --</option>
                @foreach ($tables as $table)
                    <option value="{{ $table->id }}">{{ $table->name ?? 'Table ' . $table->id }}</option>
                @endforeach
            </select>
            @error('selectedTable')
                <span class="text-red-500">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="name">Customer Name:</label>
            <input type="text" wire:model="name" id="name" class="w-full border rounded p-2" />
            @error('name')
                <span class="text-red-500">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="mobile">Mobile:</label>
            <input type="text" wire:model="mobile" id="mobile" class="w-full border rounded p-2" />
            @error('mobile')
                <span class="text-red-500">{{ $message }}</span>
            @enderror
        </div>

        <div class="mb-4">
            <label for="booking_time">Booking Time:</label>
            <input type="datetime-local" wire:model="booking_time" id="booking_time"
                class="w-full border rounded p-2" />
            @error('booking_time')
                <span class="text-red-500">{{ $message }}</span>
            @enderror
        </div>
        <div class="flex gap-3">
        <x-form.button type="submit" title="Book Now" wireTarget="saveBooking" />
        <x-form.button :route="'restaurant.advance-booking'" class="bg-gray-500 text-white">
            Back
        </x-form.button>
        </div>
    </form>
</div>
