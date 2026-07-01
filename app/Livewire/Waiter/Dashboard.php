<?php

namespace App\Livewire\Waiter;

use App\Models\Order;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\{Table, Restaurant, TableBooking};
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class Dashboard extends Component
{
    public $showConfirm = false;
    public $selectedTable = null;
    public $tables, $pickupOrders;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $tablesByArea = $this->tables->groupBy(function ($table) {
            return $table->area->name ?? 'No Area';
        });

        return view('livewire.waiter.dashboard', [
            'tablesByArea' => $tablesByArea,
        ]);
    }

    public function mount()
    {
        if (auth()->user()->restaurant_id) {
            $restaurantId = auth()->user()->restaurant_id;
        } else {
            $restaurantId = Restaurant::where('user_id', auth()->id())->value('id');
        }

        $this->tables = Table::with('area')
            ->where('restaurant_id', $restaurantId)
            ->where('is_active', 0)
            ->where(function ($query) {
                $query->whereNull('area_id')
                    ->orWhereHas('area', fn($q) => $q->where('is_active', 0));
            })
            ->get();

        $this->pickupOrders = Order::where('restaurant_id', $restaurantId)
            ->where('order_type', 'takeaway')
            ->where('status', 'pending')
            ->count();
    }

    public function openConfirm($tableId)
    {
        $this->selectedTable = Table::findOrFail($tableId);
        $now = Carbon::now();
        $estimatedEnd = $now->copy()->addMinutes(90);

        $hasConflict = TableBooking::where('table_id', $this->selectedTable->id)
        ->where('status', 'booked')
        ->where('booking_time', '<', $estimatedEnd)
        ->whereRaw("DATE_ADD(booking_time, INTERVAL 90 MINUTE) > ?", [$now])
        ->exists();

        if ($hasConflict) {
            session()->flash('error', '⚠️ This table is already reserved during your dining time!');
        } else {
            $this->showConfirm = true;
        }
    }

    public function editTable($tableId)
    {
        return redirect()->route('restaurant.item', [
            'table_id' => $tableId,
            'mode'     => 'edit'
        ]);
    }

    public function printTableBill($tableId)
{
    $order = Order::where('table_id', $tableId)
        ->where('status', 'pending')
        ->latest()
        ->first();

    if ($order) {
        $this->dispatch('btPrintBill', orderId: $order->id);
    } else {
        session()->flash('error', 'No active order found for this table.');
    }
}

}
