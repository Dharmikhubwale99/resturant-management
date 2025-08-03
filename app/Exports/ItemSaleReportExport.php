<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ItemSaleReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $fromDate, $toDate;
    protected $data;
    protected $counter = 0;

    public function __construct($fromDate, $toDate)
    {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
    }

    public function collection()
    {
        $restaurantId = Auth::user()->restaurants()->first()->id;

        $this->data = DB::table('order_items')
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

        return $this->data;
    }

    public function headings(): array
    {
        return [
            'Sr No',
            'Item Name',
            'Category',
            'Total Quantity',
            'Total Amount (₹)',
        ];
    }

    public function map($row): array
    {
        $this->counter++;

        return [
            $this->counter,
            $row->item_name,
            $row->category_name ?? 'N/A',
            $row->total_qty,
            number_format($row->total_amount, 2),
        ];
    }
}
