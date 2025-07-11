<?php

namespace App\Livewire\Kitchen;

use Livewire\Component;
use Livewire\Attributes\Layout;

class Dashboard extends Component
{
    public $activeTab = 'pending';
    public $resturant;
    #[Layout('components.layouts.kitchen.app')]
    public function render()
    {
        $this->resturant = auth()->user()->restaurants()->first();

        return view('livewire.kitchen.dashboard', [
        'pendingKots'    => \App\Models\Kot::where('status', 'pending')->latest()->get(),
        'preparingKots' => \App\Models\Kot::where('status', 'preparing')->latest()->get(),
        'readyKots' => \App\Models\Kot::where('status', 'ready')->latest()->get(),
        'cancelledKots' => \App\Models\Kot::where('status', 'cancelled')->latest()->get(),
        ]);
    }
}
