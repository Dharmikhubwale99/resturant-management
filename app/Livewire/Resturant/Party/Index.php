<?php

namespace App\Livewire\Resturant\Party;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\{Restaurant, Payment, Customer};

class Index extends Component
{
    public $parties;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $restaurantId = auth()->user()->restaurant_id ?: Restaurant::where('user_id', auth()->id())->value('id');

        $this->parties = Customer::where('restaurant_id', $restaurantId)
            ->latest()
            ->get();


        return view('livewire.resturant.party.index');
    }

    public function mount()
    {
        if (!setting('party')) {
            abort(403, 'You do not have access to this module.');
        }
    }
}
