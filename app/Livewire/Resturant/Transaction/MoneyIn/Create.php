<?php

namespace App\Livewire\Resturant\Transaction\MoneyIn;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\{Customer, Restaurant, Payment};
use App\Enums\{PaymentMethod};

class Create extends Component
{
    public $customerId = [];
    public $restaurantId;
    public $amount;
    public $paymentMethod = 'cash';
    public $selectedCustomerId;
    public $date;
    public $method = 'cash';

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.transaction.money-in.create');
    }

    public function mount()
    {
        $this->restaurantId = auth()->user()->restaurant_id ?: Restaurant::where('user_id', auth()->id())->value('id');

        $this->customerId = Customer::where('restaurant_id', $this->restaurantId)
            ->pluck('name','id')
            ->toArray();

        $this->paymentMethod = collect(PaymentMethod::cases())
            ->filter(fn($case) => in_array($case->value, ['cash', 'card', 'upi']))
            ->mapWithKeys(fn($case) => [$case->value => $case->label()])
            ->toArray();
        $this->date = now()->format('Y-m-d');
    }

    public function save()
    {
        $this->validate([
            'selectedCustomerId' => 'required|exists:customers,id',
            'method' => 'required|in:cash,card,upi',
            'amount' => 'required|numeric|min:1',
            'date' => 'required|date',
        ]);

        Payment::create([
            'restaurant_id' => $this->restaurantId,
            'customer_id' => $this->selectedCustomerId,
            'method' => $this->method,
            'amount' => $this->amount,
            'payment_date' => $this->date,
        ]);

        session()->flash('success', 'Payment saved successfully!');
        return redirect()->route('restaurant.money-maintain');
    }
}
