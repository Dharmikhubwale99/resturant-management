<?php

namespace App\Livewire\Resturant\Kitchen;

use App\Models\Kot;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Dashboard extends Component
{
    public $activeTab = 'pending';
    public $resturant;
    public $showCancelModal = false;
    public $itemToCancel = null;
    public $cancelReason = '';
    public $dateFilter = 'today';
    
    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $user = auth()->user();
        $isAdmin = $user->hasRole('admin'); 

        $this->resturant = $user->restaurants()->first();

        $date = $this->dateFilter === 'all' && $isAdmin ? null : now()->toDateString();

        $baseQuery = fn($status) => Kot::whereHas('items', fn($q) => $q->where('status', $status))
            ->when($date, fn($q) => $q->whereDate('created_at', $date))
            ->with(['items' => fn($q) => $q->where('status', $status), 'table.area'])
            ->latest()
            ->get();

        return view('livewire.resturant.kitchen.dashboard', [
            'pendingKots' => $baseQuery('pending'),
            'preparingKots' => $baseQuery('preparing'),
            'readyKots' => $baseQuery('ready'),
            'cancelledKots' => $baseQuery('cancelled'),
            'isAdmin' => $isAdmin,
        ]);
    }

    public function updateKotStatus($kotId)
    {
        $kot = Kot::with('items')->find($kotId);
        if (!$kot) {
            return;
        }

        $itemStatuses = $kot->items->pluck('status')->unique()->toArray();

        if ($kot->status === 'pending' && in_array('preparing', $itemStatuses)) {
            
            foreach ($kot->items as $item) {
                if ($item->status === 'preparing') {
                    $item->status = 'ready';
                    $item->save();
                }
            }

            $this->dispatch('kotStatusUpdated');
            return;
        }

        if ($kot->status === 'preparing' && in_array('pending', $itemStatuses)) {
            return; 
        }

        $nextStatus = match ($kot->status) {
            'pending' => 'preparing',
            'preparing' => 'ready',
            default => null,
        };

        if ($nextStatus) {
            $kot->status = $nextStatus;
            $kot->save();

            foreach ($kot->items as $item) {
                if (in_array($item->status, ['cancelled', 'ready'])) {
                    continue;
                }

                if ($kot->status === 'preparing' && $item->status === 'pending') {
                    $item->status = 'preparing';
                    $item->save();
                }

                if ($kot->status === 'ready' && $item->status === 'preparing') {
                    $item->status = 'ready';
                    $item->save();
                }
            }

            $this->dispatch('kotStatusUpdated');
        }
    }

    public function updateKotItemStatus($itemId)
    {
        $item = \App\Models\KOTItem::find($itemId);
        if (!$item) {
            return;
        }
        $nextStatus = match ($item->status) {
            'pending' => 'preparing',
            'preparing' => 'ready',
            default => null,
        };
        if ($nextStatus) {
            $item->status = $nextStatus;
            $item->save();
            $this->dispatch('kotItemStatusUpdated');
        }
    }

    // Stub for item cancel
    public function showCancelItemModal($itemId)
    {
        $this->itemToCancel = $itemId;
        $this->showCancelModal = true;
    }

    public function cancelKotItem()
    {
        $this->validate([
            'cancelReason' => 'required|string|min:3',
        ]);

        $item = \App\Models\KOTItem::find($this->itemToCancel);
        if ($item) {
            $item->status = 'cancelled';
            $item->reason = $this->cancelReason;
            $item->save();
            
            // Check if all items in the KOT are now ready or cancelled
            $this->checkKotStatus($item->kot_id);
            
            $this->dispatch('kotItemStatusUpdated');
        }

        $this->reset(['showCancelModal', 'itemToCancel', 'cancelReason']);
    }

    public function checkKotStatus($kotId)
    {
        $kot = Kot::with('items')->find($kotId);

        if (!$kot) {
            return;
        }

        $itemStatuses = $kot->items->pluck('status')->unique()->toArray();

        if (!in_array('pending', $itemStatuses) && !in_array('preparing', $itemStatuses)) {
            // All items are either ready or cancelled
            if (in_array('ready', $itemStatuses)) {
                $kot->status = 'ready';
            } elseif (in_array('cancelled', $itemStatuses)) {
                $kot->status = 'cancelled';
            }
            $kot->save();
            $this->dispatch('kotStatusUpdated');
        }
    }
}
