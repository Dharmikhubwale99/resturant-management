<?php

namespace App\Livewire\Resturant;

use App\Models\Payment;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\PaymentReportExport;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\RestaurantPaymentLog;

class PaymentReport extends Component
{
    use WithPagination;

    public $fromDate;
    public $toDate;
    public $filterType = 'today';
    public $paymentMethod = 'all';

    #[Layout('components.layouts.resturant.app')]
    public function render()
{
    $restaurantId = Auth::user()->restaurants()->first()->id;

    $payments = Payment::whereHas('order', function ($q) use ($restaurantId) {
        $q->where('restaurant_id', $restaurantId);
    })->whereBetween('created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59'])
    ->latest()
    ->paginate(10);

    $logs = RestaurantPaymentLog::whereBetween('created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59'])
        ->where('restaurant_id', $restaurantId)
        ->get();

    $processedLogs = collect();

    foreach ($logs as $log) {
    if ($log->method === 'due') {
        // Directly push as due
        $processedLogs->push((object)[
            'id' => $log->id,
            'created_at' => $log->created_at,
            'amount' => $log->amount,
            'method' => 'due',
            'customer_name' => $log->customer_name,
            'mobile' => $log->mobile,
        ]);
    } elseif ($log->amount > $log->paid_amount) {
        if ($log->paid_amount > 0) {
            $processedLogs->push((object)[
                'id' => $log->id,
                'created_at' => $log->created_at,
                'amount' => $log->paid_amount,
                'method' => $log->method,
                'customer_name' => $log->customer_name,
                'mobile' => $log->mobile,
            ]);
        }
        $processedLogs->push((object)[
            'id' => $log->id,
            'created_at' => $log->created_at,
            'amount' => $log->amount - $log->paid_amount,
            'method' => 'due',
            'customer_name' => $log->customer_name,
            'mobile' => $log->mobile,
        ]);
    } else {
        $processedLogs->push((object)[
            'id' => $log->id,
            'created_at' => $log->created_at,
            'amount' => $log->paid_amount,
            'method' => $log->method,
            'customer_name' => $log->customer_name,
            'mobile' => $log->mobile,
        ]);
    }
}


    return view('livewire.resturant.payment-report', [
        'payments' => $payments,
        'logs' => $processedLogs,
        'paymentMethod' => $this->paymentMethod,
    ]);
}


    public function mount()
    {
        $this->setDefaultDates();
    }

    public function setDefaultDates()
    {
        $today = now()->format('Y-m-d');
        $this->fromDate = $today;
        $this->toDate = $today;
    }

    public function updatedFilterType()
    {
        $this->resetPage();
        switch ($this->filterType) {
            case 'weekly':
                $this->fromDate = now()->startOfWeek()->format('Y-m-d');
                $this->toDate = now()->endOfWeek()->format('Y-m-d');
                break;
            case 'monthly':
                $this->fromDate = now()->startOfMonth()->format('Y-m-d');
                $this->toDate = now()->endOfMonth()->format('Y-m-d');
                break;
            case 'custom':
                break;
            default:
                $this->setDefaultDates();
                break;
        }
    }

    public function updatedFromDate() { $this->resetPage(); }
    public function updatedToDate() { $this->resetPage(); }
    public function updatedPaymentMethod() { $this->resetPage(); }

    public function getPaymentsProperty()
    {
        $restaurantId = Auth::user()->restaurants()->first()->id;

        $query = Payment::whereHas('order', function ($q) use ($restaurantId) {
            $q->where('restaurant_id', $restaurantId);
        })->whereBetween('created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59']);

        if ($this->paymentMethod !== 'all') {
            $query->where('method', $this->paymentMethod);
        }

        return $query->latest()->paginate(10);
    }

    public function getTotalAmountProperty()
    {
        $restaurantId = Auth::user()->restaurants()->first()->id;

        // Payments
        $query = Payment::whereHas('order', function ($q) use ($restaurantId) {
            $q->where('restaurant_id', $restaurantId);
        })->whereBetween('created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59']);

        if ($this->paymentMethod !== 'all') {
            $query->where('method', $this->paymentMethod);
        }
        $paymentSum = $query->sum('amount');

        // RestaurantPaymentLog
        $logSum = RestaurantPaymentLog::whereBetween('created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59'])
            ->where('restaurant_id', $restaurantId)
            ->sum('paid_amount');

        return $paymentSum + $logSum;
    }

    public function exportExcel()
    {
        $restaurantId = Auth::user()->restaurants()->first()->id;
        return Excel::download(
            new PaymentReportExport(
                $this->fromDate,
                $this->toDate,
                $restaurantId,
                $this->paymentMethod
            ),
            'payment_report.xlsx'
        );
    }

    public function exportPdf()
    {
        $restaurantId = Auth::user()->restaurants()->first()->id;

        $query = Payment::whereHas('order', function ($q) use ($restaurantId) {
            $q->where('restaurant_id', $restaurantId);
        })->whereBetween('created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59']);

        if ($this->paymentMethod !== 'all') {
            $query->where('method', $this->paymentMethod);
        }
        $payments = $query->get();

        $logs = RestaurantPaymentLog::whereBetween('created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59'])
            ->where('restaurant_id', $restaurantId)
            ->get();

        $totalAmount = $payments->sum('amount') + $logs->sum('paid_amount');

        $pdf = Pdf::loadView('livewire.pdf.payment-report-pdf', [
            'payments' => $payments,
            'logs' => $logs,
            'totalAmount' => $totalAmount,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'payment_report.pdf');
    }
}
