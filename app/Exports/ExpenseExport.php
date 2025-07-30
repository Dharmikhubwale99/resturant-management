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
        return Expense::with('expenseType')
            ->where('restaurant_id', $this->restaurantId)
            ->whereBetween('paid_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59'])
            ->get();
    }

    public function headings(): array
    {
        return [
            'Sr No',
            'Date',
            'Party Name',
            'Expense Type',
            'Amount Paid',
        ];
    }

    public function map($expense): array
    {
        return [
            $expense->id,
            \Carbon\Carbon::parse($expense->paid_at)->format('d-m-Y'),
            $expense->name,
            $expense->expenseType->name ?? '-',
            number_format($expense->amount, 2),
        ];
    }
}
