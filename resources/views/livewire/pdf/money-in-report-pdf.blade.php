<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Money In Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: center; }
        th { background: #eee; }
        h2, p { text-align: center; }
    </style>
</head>
<body>

    <h2>Money In Report</h2>
    <p>
        From {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }}
        to {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Sr No</th>
                <th>Date</th>
                <th>Order ID</th>
                <th>Party Name</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Total</th>
                <th>Payment Type</th>
                <th>Payment Info</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($orders as $index => $order)
                @php
                    $logs = $order->paymentLogs;
                    $method = optional($order->payment)->method;

                    if ($method === 'part' && $order->paymentGroups->count()) {
                        $paymentDetails = $order->paymentGroups->groupBy('method')->map(fn($group, $m) =>
                            ucfirst($m) . ': ₹' . $group->sum('amount')
                        )->implode(' | ');
                    } elseif ($logs->isNotEmpty()) {
                        $paymentDetails = $logs->groupBy('method')->map(fn($group, $m) =>
                            ucfirst($m) . ': ₹' . $group->sum('paid_amount') . ' | ₹' . $group->sum('amount')
                        )->implode(' | ');
                    } else {
                        $paymentDetails = ucfirst($method) . ' : ₹' . $order->total_amount;
                    }
                @endphp

                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d-m-Y') }}</td>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->customer_name ?? ($order->table->name ?? 'N/A') }}</td>
                    <td>{{ $order->mobile ?? '-' }}</td>
                    <td>{{ ucfirst($order->status) }}</td>
                    <td>₹{{ number_format($order->total_amount, 2) }}</td>
                    <td>{{ $method ?? '-' }}</td>
                    <td>{{ $paymentDetails }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
