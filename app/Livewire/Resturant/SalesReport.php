<?php

namespace App\Livewire\Resturant;

use App\Models\Order;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SalesReportExport;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesReport extends Component
{
    use WithPagination;

    public $fromDate;
    public $toDate;
    public $filterType = 'today'; 

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.sales-report', [
            'orders' => $this->orders,
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
                // Keep manual input active
                break;
            default:
                $this->setDefaultDates();
                break;
        }
    }

    public function updatedFromDate()
    {
        $this->resetPage();
    }

    public function updatedToDate()
    {
        $this->resetPage();
    }

    public function getOrdersProperty()
    {
        $restaurantId = Auth::user()->restaurants()->first()->id;

        return Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59'])
            ->latest()
            ->paginate(10);
    }

    public function getTotalAmountProperty()
    {
        $restaurantId = Auth::user()->restaurants()->first()->id;

        return Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59'])
            ->sum('total_amount');
    }

    public function exportExcel()
    {
        $restaurantId = Auth::user()->restaurants()->first()->id;
        return Excel::download(
            new SalesReportExport($this->fromDate, $this->toDate, $restaurantId),
            'sales_report.xlsx'
        );
    }

    public function exportPdf()
    {
        $restaurantId = Auth::user()->restaurants()->first()->id;
        $orders = Order::where('restaurant_id', $restaurantId)
            ->whereBetween('created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59'])
            ->get();

        $totalAmount = $orders->sum('total_amount');

        $pdf = Pdf::loadView('livewire.pdf.sales-report-pdf', [
            'orders' => $orders,
            'totalAmount' => $totalAmount,
        ]);

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'sales_report.pdf');
    }
}

