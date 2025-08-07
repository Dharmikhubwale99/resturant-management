<?php

namespace App\Livewire\Resturant\Transaction\MoneyOut;

use Livewire\Component;
use App\Models\{MoneyOut, Restaurant};
use Livewire\Attributes\Layout;

class MoneyOutForm extends Component
{
    public $party_name;
    public $amount;
    public $description;
    public $date;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.transaction.money-out.money-out-form');
    }

    public function mount()
    {
        if (!setting('moneyOut')) {
            abort(403, 'You do not have access to this module.');
        }

        $this->date = now()->format('Y-m-d');
    }

    public function save()
    {
        $this->validate([
            'amount' => 'required|numeric|min:0.01',
            'party_name' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'date' => 'required|date',
        ]);

        if (auth()->user()->restaurant_id) {
            $restaurantId = auth()->user()->restaurant_id;
        } else {
            $restaurantId = Restaurant::where('user_id', auth()->id())->value('id');
        }

        MoneyOut::create([
            'restaurant_id' => $restaurantId,
            'party_name' => $this->party_name,
            'amount' => $this->amount,
            'description' => $this->description,
            'date' => $this->date,
        ]);

        session()->flash('success', 'Money Out entry saved.');
        $this->reset(['party_name', 'amount', 'description', 'date']);
        return redirect()->route('resturant.transaction.money-out');
    }
}
