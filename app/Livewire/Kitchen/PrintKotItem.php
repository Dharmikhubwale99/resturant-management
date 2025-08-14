<?php

namespace App\Livewire\Kitchen;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\{Kot, KOTItem};

class PrintKotItem extends Component
{
    public Kot $kot;
    public KOTItem $item;
    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.kitchen.print-kot-item');
    }

    public function mount(Kot $kot, KOTItem $item)
    {
        abort_unless($item->kot_id === $kot->id, 404);

        $user = auth()->user();
        abort_unless($user && $user->restaurant_id === $kot->restaurant_id, 403);

        $kot->load(['table.area', 'order']);
        $this->kot  = $kot;
        $this->item = $item;
    }
}
