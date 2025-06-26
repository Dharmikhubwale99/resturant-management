<?php

namespace App\Livewire\Resturant;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;

class Dashboard extends Component
{
    public $user;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.dashboard');
    }

    public function mount()
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->first();

        if (empty($restaurant->name) || empty($restaurant->email) || empty($restaurant->mobile) || empty($restaurant->address) || empty($restaurant->pin_code_id)) {
            return redirect()->route('resturant.register')->with('info', 'Please complete your restaurant profile.');
        }
    }
}
