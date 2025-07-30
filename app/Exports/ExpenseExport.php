<?php

namespace App\Exports;

use App\Models\Expense;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ExpenseExport implements FromCollection, WithHeadings, WithMapping
{
    protected $fromDate, $toDate, $restaurantId;

    public function __construct($fromDate, $toDate, $restaurantId)
    {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->restaurantId = $restaurantId;
    }

    public function collection()
    {
        return Expense::where('restaurant_id', $this->restaurantId)
            ->whereBetween('created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59'])
            ->get(['id', 'created_at', 'total_amount']);
    }

    public function headings(): array
    {
        return [
            'Order No',
            'Date',
            'Total Amount',
        ];
    }

}
