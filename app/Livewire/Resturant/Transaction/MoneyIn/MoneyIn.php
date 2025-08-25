<?php

namespace App\Livewire\Resturant\Transaction\MoneyIn;

use App\Models\{Order, Restaurant, RestaurantPaymentLog};
use App\Models\Payment;
use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Traits\TransactionTrait;
use Livewire\WithPagination;

class MoneyIn extends Component
{
    use TransactionTrait, WithPagination;
    public $orders;
    public $showModal = false;
    public $selectedLogId;
    public $newPaidAmount = 0;
    public $newMethod = '';
    public $newIssue = '';
    public $firstLogForPaymentId = [];
    public $search = '';
    public $fromDate = null;
    public $toDate = null;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $restaurantId = auth()->user()->restaurant_id ?: Restaurant::where('user_id', auth()->id())->value('id');

        $logs = Payment::query()
            ->whereIn('method', ['duo', 'part', 'cash', 'card', 'upi'])
            ->where('restaurant_id', $restaurantId)
            // eager loads
            ->with([
                'order',
                'customer',
                'logs' => fn ($q) => $q->latest(),
            ])
            // paid sum helper (for 'duo'/'part' etc.)
            ->withSum(['logs as paid_sum' => function ($q) {
                // date-range scoped sum (only if filter applied)
                if ($this->fromDate) {
                    $q->whereDate('created_at', '>=', $this->fromDate);
                }
                if ($this->toDate) {
                    $q->whereDate('created_at', '<=', $this->toDate);
                }
            }], 'paid_amount')

            // DATE FILTER (payments table date)
            ->when($this->fromDate, fn ($q) => $q->whereDate('created_at', '>=', $this->fromDate))
            ->when($this->toDate, fn ($q) => $q->whereDate('created_at', '<=', $this->toDate))

            // SEARCH: customer name, mobile, or paid amount
            ->when($this->search !== '', function ($q) {
                $term = trim($this->search);

                $q->where(function ($qq) use ($term) {
                    // Customer name / mobile
                    $qq->whereHas('customer', function ($qc) use ($term) {
                        $qc->where('name', 'like', "%{$term}%")
                           ->orWhere('mobile', 'like', "%{$term}%");
                    })
                    // fallback: try on latest log customer_name/mobile too
                    ->orWhereHas('logs', function ($ql) use ($term) {
                        $ql->where('customer_name', 'like', "%{$term}%")
                           ->orWhere('mobile', 'like', "%{$term}%");
                    });

                    // If numeric -> search by paid amount (supports both single & duo payments)
                    if (is_numeric($term)) {
                        $amount = (float) $term;

                        $qq->orWhere(function ($q2) use ($amount) {
                            // one-shot methods typically store amount on payments.amount
                            $q2->where('amount', $amount);
                        })
                        ->orWhereHas('logs', function ($ql) use ($amount) {
                            // any log exact paid value
                            $ql->where('paid_amount', $amount);
                        })
                        // also allow LIKE for rough matches (e.g., "200."/"2000")
                        ->orWhere('amount', 'like', "%{$amount}%");
                    }
                });
            })
            ->latest()
            ->paginate(10);

        return view('livewire.resturant.transaction.money-in.money-in', [
            'logs' => $logs,
        ]);
    }

    public function updatedSearch()   { $this->resetPage(); }
    public function updatedFromDate() { $this->resetPage(); }
    public function updatedToDate()   { $this->resetPage(); }

    public function mount()
    {
        if (!setting('moneyIn')) {
            abort(403, 'You do not have access to this module.');
        }
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
