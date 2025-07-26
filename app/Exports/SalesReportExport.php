<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;

class SalesReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $fromDate, $toDate, $restaurantId;

    public function __construct($fromDate, $toDate, $restaurantId)
    {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->restaurantId = $restaurantId;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Order::where('restaurant_id', $this->restaurantId)
            ->whereBetween('created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59'])
            ->get(['id', 'created_at', 'total_amount']);
    }

    // Add this method for column headers
    public function headings(): array
    {
        return [
            'Order No',
            'Date',
            'Total Amount',
        ];
    }

    // Add this method to format the date
    public function map($order): array
    {
        return [
            $order->id,
            \Carbon\Carbon::parse($order->created_at)->format('d-m-Y'),
            $order->total_amount,
        ];
    }
}
