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
        $this->table = Table::with('area')->findOrFail($id);
    }

    public function render()
    {
        return view('livewire.resturant.table.show');
    }
} 