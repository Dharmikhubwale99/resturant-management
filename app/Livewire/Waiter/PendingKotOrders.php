<?php

namespace App\Livewire\Waiter;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Kot;
use App\Models\KotItem;

class PendingKotOrders extends Component
{
    public array $orders = [];
    public string $status = 'pending'; 
    public array $openItems = [];

    #[Layout('components.layouts.waiter.app')]
    public function render()
    {
        return view('livewire.waiter.pending-kot-orders');
    }

    public function mount()
    {
        $this->loadOrders();
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->openItems = [];
        $this->loadOrders();
    }

    public function loadOrders(): void
    {
        $this->orders = Kot::with(['items', 'table.area']) 
            ->where('status', $this->status)
            ->latest()
            ->get()
            ->map(function ($kot) {
                return [
                    'id'           => $kot->id,
                    'kot_number'   => $kot->kot_number,
                    'table_name'   => optional($kot->table)->name ?? '',
                    'area_name'    => optional($kot->table->area)->name ?? '',
                    'created_at'   => $kot->created_at,
                    'items_count'  => $kot->items->sum('quantity'),
                    'status'       => $kot->status,
                ];
            })
            ->toArray();
    }

    public function toggleShow(int $kotId): void
    {
        if (isset($this->openItems[$kotId])) {
            unset($this->openItems[$kotId]);
            return;
        }

        $this->openItems[$kotId] = KotItem::with('item')
            ->where('kot_id', $kotId)
            ->get();
    }

    protected $listeners = ['kotUpdated' => 'loadOrders'];
}
