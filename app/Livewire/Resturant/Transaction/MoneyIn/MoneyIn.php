<?php

namespace App\Livewire\Resturant\Transaction\MoneyIn;

use App\Models\{Order, Restaurant, RestaurantPaymentLog};
use App\Models\Payment;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Traits\TransactionTrait;

class MoneyIn extends Component
{
    use TransactionTrait;
    public $orders;
    public $logs;
    public $showModal = false;
    public $selectedLogId;
    public $newPaidAmount = 0;
    public $newMethod = '';
    public $newIssue = '';
    public $firstLogForPaymentId = [];

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $restaurantId = auth()->user()->restaurant_id ?: Restaurant::where('user_id', auth()->id())->value('id');

        $this->logs = Payment::whereIn('method', ['duo', 'part', 'cash', 'card', 'upi'])
            ->where('restaurant_id', $restaurantId)
            ->with(['order','customer', 'logs' => fn($q) => $q->latest()])
            ->latest()
            ->get();

        return view('livewire.resturant.transaction.money-in.money-in');
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

        $recivable = $log->amount - $this->newPaidAmount;

        $relatedLogs = RestaurantPaymentLog::where('payment_id', $log->payment_id)->get();

        // foreach ($relatedLogs as $entry) {
        //     $entry->update([
        //         'paid_amount' => $entry->paid_amount + $this->newPaidAmount,
        //         'amount' => $recivable,
        //     ]);
        // }

        $newRemaining = $recivable <= 0 ? 0 : $recivable;

        RestaurantPaymentLog::create([
            'restaurant_id' => $log->restaurant_id,
            'payment_id' => $log->payment_id,
            'order_id' => $log->order_id,
            'customer_name' => $log->customer_name,
            'mobile' => $log->mobile,
            'amount' => $newRemaining,
            'paid_amount' => $this->newPaidAmount,
            'method' => $this->newMethod,
            'issue' => $this->newIssue,
        ]);

        $this->totalSale($log->restaurant_id, $this->newPaidAmount);
        $this->reset(['showModal', 'selectedLogId', 'newPaidAmount', 'newMethod', 'newIssue']);

        session()->flash('success', 'Follow-up payment saved!');
    }
}
