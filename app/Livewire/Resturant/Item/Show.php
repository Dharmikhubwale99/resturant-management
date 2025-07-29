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
        if (!setting('item')) {
            abort(403, 'You do not have access to this module.');
        }
        $this->item = Item::with(['variants', 'media'])->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.resturant.item.show');
    }
}
