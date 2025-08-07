<?php

namespace App\Livewire\Waiter\AdvancBook;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\{Table, TableBooking, Order};

class Index extends Component
{
    public $reservedTables;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $this->reservedTables = TableBooking::where('status', 'booked')->get();

        return view('livewire.waiter.advanc-book.index', [
            'reservedTables' => $this->reservedTables
        ]);
    }

    public function startOrder($bookingId)
    {
        $booking = TableBooking::with(['customer', 'table'])->findOrFail($bookingId);

        $existingOrder = Order::where('table_id', $booking->table_id)
            ->where('customer_id', $booking->customer_id)
            ->whereDate('created_at', today())
            ->first();

        $booking->update(['status' => 'done']);
        if (!$existingOrder) {
            Order::create([
                'restaurant_id' => $booking->restaurant_id,
                'table_id' => $booking->table_id,
                'customer_id' => $booking->customer_id,
                'status' => 'pending',
            ]);
        }

        return redirect()->route('restaurant.item', ['table_id' => $booking->table_id]);
    }

}
