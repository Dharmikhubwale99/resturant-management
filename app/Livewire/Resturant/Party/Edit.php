<?php

namespace App\Livewire\Resturant\Party;

use Livewire\Component;
use App\Models\{Restaurant, Customer};
use Livewire\Attributes\Layout;

class Edit extends Component
{
    public $customer;
    public $customerId = [];
    public $selectedCustomerId;
    public $name;
    public $mobile;
    public $restaurantId;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.party.edit');
    }

    public function mount($id)
    {
        if (!setting('party')) {
            abort(403, 'You do not have access to this module.');
        }

        $this->restaurantId = auth()->user()->restaurant_id ?: Restaurant::where('user_id', auth()->id())->value('id');
        $this->customer = Customer::findOrFail($id);
        $this->name = $this->customer->name;
        $this->mobile = $this->customer->mobile;
    }

    public function update()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|string|max:15',
        ]);

        $this->customer->update([
            'name' => $this->name,
            'mobile' => $this->mobile,
        ]);

        session()->flash('message', 'Party updated successfully.');
        return redirect()->route('restaurant.party');
    }
}
