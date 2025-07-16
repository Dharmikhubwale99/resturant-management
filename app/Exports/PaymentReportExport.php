<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMapping;

class PaymentReportExport implements FromCollection, WithHeadings, WithMapping
{
    protected $fromDate, $toDate, $restaurantId, $paymentMethod;

    public function __construct($fromDate, $toDate, $restaurantId, $paymentMethod = 'all')
    {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->restaurantId = $restaurantId;
        $this->paymentMethod = $paymentMethod;
    }

    public function collection()
    {
        $query = Payment::whereHas('order', function ($q) {
            $q->where('restaurant_id', $this->restaurantId);
        })->whereBetween('created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59']);

        if ($this->paymentMethod !== 'all') {
            $query->where('method', $this->paymentMethod);
        }

        return $query->get(['id', 'created_at', 'amount', 'method']);
    }

    public function headings(): array
    {
        return ['Payment No', 'Date', 'Amount', 'Method'];
    }

    public function map($row): array
    {
        return [
            $row->id,
            \Carbon\Carbon::parse($row->created_at)->format('d-m-Y'),
            $row->amount,
            $row->method,
        ];
    }
}
