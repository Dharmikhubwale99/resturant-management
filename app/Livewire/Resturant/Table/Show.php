<?php

namespace App\Livewire\Resturant\Table;

use App\Models\Table;
use Livewire\Component;
use Livewire\Attributes\Layout;

class Show extends Component
{
    public $table;

    #[Layout('components.layouts.resturant.app')]
    public function mount($id)
    {
        if (!setting('table')) {
            abort(403, 'You do not have access to this module.');
        }

        $this->table = Table::with('area')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.resturant.table.show');
    }
}
