<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Facades\Auth;

class MoneyInExport implements FromCollection, WithHeadings, WithMapping
{
    protected $fromDate, $toDate, $methodFilter, $restaurantId;

    public function __construct($fromDate, $toDate, $restaurantId, $methodFilter = null)
    {
        $this->fromDate = $fromDate;
        $this->toDate = $toDate;
        $this->methodFilter = $methodFilter;
        $this->restaurantId = $restaurantId;
    }

    public function collection()
    {
        $query = Order::with(['payment', 'paymentLogs', 'table', 'paymentGroups', 'restaurant'])
            ->where('restaurant_id', $this->restaurantId)
            ->whereBetween('created_at', [$this->fromDate . ' 00:00:00', $this->toDate . ' 23:59:59']);

        if ($this->methodFilter) {
            $query->whereHas('payment', function ($q) {
                $q->where('method', $this->methodFilter);
            });
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Date',
            'Order ID',
            'Party Name',
            'Phone',
            'Status',
            'Total',
            'Payment Type',
            'Paid Details',
        ];
    }

    public function map($order): array
    {
        $logs = $order->paymentLogs;
        $method = optional($order->payment)->method;

        if ($method === 'part' && $order->paymentGroups->count()) {
            $paidDetails = $order->paymentGroups
                ->groupBy('method')
                ->map(fn($g, $m) => ucfirst($m) . ': ₹' . $g->sum('amount'))
                ->implode(' | ');
        } elseif ($logs->isNotEmpty()) {
            $paidDetails = $logs->groupBy('method')->map(function ($group, $m) {
                return ucfirst($m) . ': ₹' . $group->sum('paid_amount') . ' | ₹' . $group->sum('amount');
            })->implode(' | ');
        } else {
            $paidDetails = ucfirst($method) . ' : ₹' . $order->total_amount;
        }

        return [
            $order->created_at->format('d-m-Y'),
            $order->order_number,
            $order->customer_name ?? ($order->table->name ?? 'N/A'),
            $order->mobile ?? '-',
            ucfirst($order->status),
            $order->total_amount,
            $method,
            $paidDetails,
        ];
    }
}
