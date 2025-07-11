<?php

namespace App\Livewire\Kitchen;

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
    
    #[Layout('components.layouts.kitchen.app')]
    public function render()
    {
        $this->resturant = auth()->user()->restaurants()->first();

        $pending = Kot::whereHas('items', fn($q) => $q->where('status', 'pending'))
            ->with(['items' => fn($q) => $q->where('status', 'pending'), 'table.area'])
            ->latest()
            ->get();

        $preparing = Kot::whereHas('items', fn($q) => $q->where('status', 'preparing'))
            ->with(['items' => fn($q) => $q->where('status', 'preparing'), 'table.area'])
            ->latest()
            ->get();

        $ready = Kot::whereHas('items', fn($q) => $q->where('status', 'ready'))
            ->with(['items' => fn($q) => $q->where('status', 'ready'), 'table.area'])
            ->latest()
            ->get();

        return view('livewire.kitchen.dashboard', [
            'pendingKots' => $pending,
            'preparingKots' => $preparing,
            'readyKots' => $ready,
            'cancelledKots' => Kot::with('items', 'table.area')->where('status', 'cancelled')->latest()->get(),
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
}
