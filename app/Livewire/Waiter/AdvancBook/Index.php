<?php

namespace App\Livewire\Waiter\AdvancBook;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\{Table, TableBooking, Order};
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;
    public $reservedTables;
    protected $paginationTheme = 'tailwind';

    public $search = '';

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $bookings = TableBooking::query()
            ->where('status', 'booked')
            ->with(['customer', 'table'])
            ->when($this->search !== '', function ($q) {
                $term = trim($this->search);
                $q->whereHas('customer', function ($qc) use ($term) {
                    $qc->where('name', 'like', "%{$term}%")
                       ->orWhere('mobile', 'like', "%{$term}%");
                });
            })
            ->latest('booking_time')
            ->paginate(10);

        return view('livewire.waiter.advanc-book.index', [
            'bookings' => $bookings,
        ]);
    }

    public function updatedSearch()
    {
        $this->resetPage();
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
