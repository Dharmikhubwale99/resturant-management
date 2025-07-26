<?php

namespace App\Livewire\Waiter;

use App\Models\Order;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\{Table, Restaurant};

class Dashboard extends Component
{
    public $showConfirm = false;
    public $selectedTable = null;
    public $tables, $pickupOrders;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        // Group tables by area name (or 'No Area' if null)
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
            ->get();

        $this->pickupOrders = Order::where('restaurant_id', $restaurantId)
            ->where('order_type', 'takeaway')
            ->where('status', 'pending')
            ->count();
    }

    public function openConfirm($tableId)
    {
        $this->selectedTable = Table::findOrFail($tableId);
        $this->showConfirm = true;
    }

    public function editTable($tableId)
    {
        return redirect()->route('restaurant.item', [
            'table_id' => $tableId,
            'mode'     => 'edit'
        ]);
    }

}
