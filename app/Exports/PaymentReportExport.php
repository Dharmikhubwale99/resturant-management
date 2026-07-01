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
        // Payments
        $query = Payment::whereHas('order', function ($q) {
            $q->where('restaurant_id', $this->restaurantId);
        })->whereBetween('created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59']);

        if ($this->paymentMethod !== 'all') {
            $query->where('method', $this->paymentMethod);
        }
        $payments = $query->get(['id', 'created_at', 'amount', 'method']);

        // RestaurantPaymentLog
        $logs = \App\Models\RestaurantPaymentLog::whereBetween('created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59'])
            ->where('restaurant_id', $this->restaurantId)
            ->get(['id', 'created_at', 'paid_amount', 'method', 'customer_name', 'mobile']);

        // Map logs to match payment columns
        $logsMapped = $logs->map(function ($log) {
            return [
                'id' => $log->id,
                'created_at' => $log->created_at,
                'amount' => $log->paid_amount,
                'method' => $log->method,
                'customer_name' => $log->customer_name,
                'mobile' => $log->mobile,
            ];
        });

        // Map payments to match columns
        $paymentsMapped = $payments->map(function ($row) {
            return [
                'id' => $row->id,
                'created_at' => $row->created_at,
                'amount' => $row->amount,
                'method' => $row->method,
                'customer_name' => '',
                'mobile' => '',
            ];
        });

        // Merge and return as a collection
        return $paymentsMapped->concat($logsMapped);
    }

    public function headings(): array
    {
        return ['Payment No', 'Date', 'Amount', 'Method', 'Customer Name', 'Mobile'];
    }

    public function map($row): array
    {
        return [
            $row['id'],
            \Carbon\Carbon::parse($row['created_at'])->format('d-m-Y'),
            $row['amount'],
            ucfirst($row['method']),
            $row['customer_name'],
            $row['mobile'],
        ];
    }
}
