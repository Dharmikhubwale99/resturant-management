<?php

namespace App\Livewire\Resturant\Report;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\{Restaurant, Payment, RestaurantPaymentLog};
use App\Exports\MoneyInExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Livewire\Attributes\Layout;

class MoneyIn extends Component
{
    use WithPagination;

    public $dateFilter = 'today';
    public $fromDate;
    public $toDate;
    public $methodFilter = '';
    public $showLogsForPaymentId = null;
    public $paymentLogs = [];

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.report.money-in', [
            'payments' => $this->getFilteredPayments(),
        ]);
    }

    public function mount()
    {
        if (!setting('report')) {
            abort(403, 'You do not have access to this module.');
        }
    }

    public function updatedDateFilter()
    {
        if ($this->dateFilter === 'custom') {
            $this->fromDate = now()->subDays(7)->format('Y-m-d');
            $this->toDate = now()->format('Y-m-d');
        } else {
            $this->fromDate = null;
            $this->toDate = null;
        }
    }

    public function getFilteredPayments()
    {
        $restaurantId = auth()->user()->restaurant_id
            ?: Restaurant::where('user_id', auth()->id())->value('id');

        $query = Payment::whereIn('method', ['duo', 'part', 'cash', 'card', 'upi'])
            ->where('restaurant_id', $restaurantId)
            ->with(['order', 'customer', 'logs' => fn($q) => $q->latest()]);

        if ($this->dateFilter === 'today') {
            $query->whereDate('created_at', now()->toDateString());
        } elseif ($this->dateFilter === 'weekly') {
            $query->whereBetween('created_at', [now()->subWeek(), now()]);
        } elseif ($this->dateFilter === 'monthly') {
            $query->whereMonth('created_at', now()->month);
        } elseif ($this->dateFilter === 'custom' && $this->fromDate && $this->toDate) {
            $query->whereBetween('created_at', [$this->fromDate, $this->toDate]);
        }

        if ($this->methodFilter) {
            $query->where('method', $this->methodFilter);
        }

        return $query->paginate(15);
    }

    public function showPaymentLogs($paymentId)
    {
        if ($this->showLogsForPaymentId === $paymentId) {
            $this->showLogsForPaymentId = null;
            $this->paymentLogs = [];
        } else {
            $this->showLogsForPaymentId = $paymentId;
            $this->paymentLogs = RestaurantPaymentLog::where('payment_id', $paymentId)
                ->orderByDesc('created_at')
                ->get()
                ->toArray();
        }
    }

    public function exportExcel()
    {
        $restaurantId = auth()->user()->restaurant_id
            ?: Restaurant::where('user_id', auth()->id())->value('id');

        $from = $this->fromDate ?? now()->format('Y-m-d');
        $to = $this->toDate ?? now()->format('Y-m-d');

        return Excel::download(
            new MoneyInExport($from, $to, $restaurantId, $this->methodFilter),
            'money_in_report.xlsx'
        );
    }

    public function exportPdf()
    {
        $restaurantId = auth()->user()->restaurant_id
            ?: Restaurant::where('user_id', auth()->id())->value('id');

        $from = $this->fromDate ?? now()->format('Y-m-d');
        $to = $this->toDate ?? now()->format('Y-m-d');

        $payments = Payment::whereIn('method', ['duo', 'part', 'cash', 'card', 'upi'])
            ->where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->when($this->methodFilter, function ($query) {
                $query->where('method', $this->methodFilter);
            })
            ->with(['order', 'customer', 'logs' => fn($q) => $q->latest()])
            ->get();

        $pdf = Pdf::loadView('livewire.pdf.money-in-report-pdf', [
            'payments' => $payments,
            'fromDate' => $from,
            'toDate' => $to,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'money_in_report.pdf');
    }
}
