<?php

namespace App\Livewire\Resturant\Report;

use Livewire\Component;
use Barryvdh\DomPDF\Facade\Pdf;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ItemSaleReportExport;
use Livewire\WithPagination;

class ItemSaleReport extends Component
{
    use WithPagination;

    public $fromDate, $toDate, $filterType = 'today';

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.report.item-sale-report', [
            'data' => $this->reportData,
        ]);
    }
    public function mount()
    {
        $this->setDefaultDates();
    }

    public function updatedFilterType()
    {
        switch ($this->filterType) {
            case 'weekly':
                $this->fromDate = now()->startOfWeek()->toDateString();
                $this->toDate = now()->endOfWeek()->toDateString();
                break;
            case 'monthly':
                $this->fromDate = now()->startOfMonth()->toDateString();
                $this->toDate = now()->endOfMonth()->toDateString();
                break;
            default:
                $this->setDefaultDates();
                break;
        }
    }

    public function setDefaultDates()
    {
        $this->fromDate = now()->toDateString();
        $this->toDate = now()->toDateString();
    }

    public function getReportDataProperty()
    {
        $restaurantId = Auth::user()->restaurants()->first()->id;

        $query = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id') 
            ->join('items', 'order_items.item_id', '=', 'items.id')
            ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
            ->where('order_items.status', 'served')
            ->whereBetween('order_items.created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59'])
            ->where('orders.restaurant_id', $restaurantId)
            ->select(
                'items.name as item_name',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.final_price) as total_amount')
            )
            ->groupBy('item_id', 'items.name', 'categories.name')
            ->orderByDesc(DB::raw('SUM(order_items.quantity)'));

            return $query->paginate(10);   
    }

    public function exportExcel()
    {
        return Excel::download(new ItemSaleReportExport($this->fromDate, $this->toDate), 'item_sale_report.xlsx');
    }

    public function exportPdf()
    {
        $restaurantId = Auth::user()->restaurants()->first()->id;

        $data = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('items', 'order_items.item_id', '=', 'items.id')
            ->leftJoin('categories', 'items.category_id', '=', 'categories.id')
            ->where('order_items.status', 'served')
            ->whereBetween('order_items.created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59'])
            ->where('orders.restaurant_id', $restaurantId)
            ->select(
                'items.name as item_name',
                'categories.name as category_name',
                DB::raw('SUM(order_items.quantity) as total_qty'),
                DB::raw('SUM(order_items.final_price) as total_amount')
            )
            ->groupBy('items.id', 'items.name', 'categories.name')
            ->orderByDesc(DB::raw('SUM(order_items.quantity)'))
            ->get();

        $pdf = Pdf::loadView('livewire.pdf.item-sale-report-pdf', [
            'data' => $data,
            'fromDate' => $this->fromDate,
            'toDate' => $this->toDate,
        ])->setPaper('a4', 'portrait');

        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'item_sale_report.pdf');
    }
}
