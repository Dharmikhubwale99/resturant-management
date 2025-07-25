<?php

namespace App\Livewire\Resturant\Item;

use App\Models\Item;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Show extends Component
{
    public $item;

    #[Layout('components.layouts.resturant.app')]
    public function mount($id)
    {
        $this->item = Item::with(['variants', 'media'])->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.resturant.item.show');
    }
}
