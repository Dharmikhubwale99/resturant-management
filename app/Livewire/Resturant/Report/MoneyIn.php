<?php

namespace App\Livewire\Resturant\Report;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;
use App\Exports\MoneyInExport;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use App\Models\RestaurantPaymentLog;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class MoneyIn extends Component
{
    use WithPagination;

    public $dateFilter = 'today';
    public $fromDate;
    public $toDate;
    public $methodFilter = '';

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.report.money-in', [
            'orders' => $this->getFilteredOrders(),
        ]);
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

    public function getFilteredOrders()
    {
                $restaurantId = Auth::user()->restaurants()->first()->id;

        $query = Order::query()->where('restaurant_id', $restaurantId)->with(['payments', 'paymentLogs']);

        if ($this->dateFilter === 'today') {
            $this->fromDate = now()->startOfDay()->format('Y-m-d');
            $this->toDate = now()->endOfDay()->format('Y-m-d');
            $query->whereDate('created_at', now()->toDateString());
        } elseif ($this->dateFilter === 'weekly') {
            $this->fromDate = now()->subWeek()->format('Y-m-d');
            $this->toDate = now()->format('Y-m-d');
            $query->whereBetween('created_at', [now()->subWeek(), now()]);
        } elseif ($this->dateFilter === 'monthly') {
            $this->fromDate = now()->startOfMonth()->format('Y-m-d');
            $this->toDate = now()->endOfMonth()->format('Y-m-d');
            $query->whereMonth('created_at', now()->month);
        } elseif ($this->dateFilter === 'custom' && $this->fromDate && $this->toDate) {
            $query->whereBetween('created_at', [$this->fromDate, $this->toDate]);
        }

        if ($this->methodFilter) {
            $query->whereHas('payment', function ($q) {
                $q->where('method', $this->methodFilter);
            });
        }

        return $query->paginate(15);
    }

    public function exportExcel()
    {
        $restaurantId = Auth::user()->restaurants()->first()->id;
        $from = $this->fromDate ?? now()->format('Y-m-d');
        $to = $this->toDate ?? now()->format('Y-m-d');

        return Excel::download(
            new MoneyInExport($from, $to, $restaurantId,$this->methodFilter),
            'money_in_report.xlsx'
        );
    }

    public function exportPdf()
    {
        $restaurantId = Auth::user()->restaurants()->first()->id;
        $from = $this->fromDate ?? now()->format('Y-m-d');
        $to = $this->toDate ?? now()->format('Y-m-d');

        $orders = Order::with(['payment', 'paymentLogs', 'paymentGroups', 'table', 'payment'])
            ->where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59'])
            ->when($this->methodFilter, function ($query) {
                $query->whereHas('payment', function ($q) {
                    $q->where('method', $this->methodFilter);
                });
            })
            ->get();

        $pdf = Pdf::loadView('livewire.pdf.money-in-report-pdf', [
            'orders' => $orders,
            'fromDate' => $from,
            'toDate' => $to,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'money_in_report.pdf');
    }
}
