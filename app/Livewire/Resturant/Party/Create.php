<?php

namespace App\Livewire\Resturant\Party;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\{Restaurant, Customer};

class Create extends Component
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
        return view('livewire.resturant.party.create');
    }

    public function mount()
    {
        $this->restaurantId = auth()->user()->restaurant_id ?: Restaurant::where('user_id', auth()->id())->value('id');
        $this->customer = Customer::where('restaurant_id', $this->restaurantId)->get();
        $this->customerId = $this->customer->pluck('name','id')->toArray();
    }

    public function save()
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'mobile' => 'required|numeric',
        ]);

        Customer::create([
            'restaurant_id' => $this->restaurantId,
            'name' => $this->name,
            'mobile' => $this->mobile,
        ]);

        session()->flash('success', 'Customer added successfully!');
        return redirect()->route('restaurant.party');
    }
}
