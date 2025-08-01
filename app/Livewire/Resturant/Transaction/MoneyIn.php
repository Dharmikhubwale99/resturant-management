<?php

namespace App\Livewire\Resturant\Transaction;

use App\Models\{Order, Restaurant, RestaurantPaymentLog};
use App\Models\Payment;
use Livewire\Component;
use Livewire\Attributes\Layout;

class MoneyIn extends Component
{
    public $orders;
    public $logs;
    public $showModal = false;
    public $selectedLogId;
    public $newPaidAmount = 0;
    public $newMethod = '';
    public $newIssue = '';

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $restaurantId = auth()->user()->restaurant_id
            ?: Restaurant::where('user_id', auth()->id())->value('id');

        $this->logs = RestaurantPaymentLog::whereHas('payment', fn($q) =>
            $q->where('method', 'duo')
        )
        ->where('restaurant_id', $restaurantId)
        ->with(['payment', 'order'])
        ->latest()
        ->get();

        return view('livewire.resturant.transaction.money-in');
    }

    public function openPaymentModal($logId)
{
    $this->selectedLogId = $logId;
    $this->showModal = true;
}

public function saveFollowUpPayment()
{
    $log = RestaurantPaymentLog::findOrFail($this->selectedLogId);

    $this->validate([
        'newPaidAmount' => 'required|numeric|min:1',
        'newMethod' => 'required|string',
    ]);

    $log->update([
        'paid_amount' => $log->paid_amount + $this->newPaidAmount,
        'amout' => $log->amount - $this->newPaidAmount,
    ]);

    RestaurantPaymentLog::create([
        'restaurant_id' => $log->restaurant_id,
        'payment_id' => $log->payment_id,
        'order_id' => $log->order_id,
        'customer_name' => $log->customer_name,
        'mobile' => $log->mobile,
        'amount' => $log->amount,
        'paid_amount' => $this->newPaidAmount,
        'method' => $this->newMethod,
        'issue' => $this->newIssue,
    ]);

    $this->reset(['showModal', 'selectedLogId', 'newPaidAmount', 'newMethod', 'newIssue']);

    session()->flash('success', 'Follow-up payment saved!');
}
}
