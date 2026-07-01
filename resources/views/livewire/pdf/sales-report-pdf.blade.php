<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Sales Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: center; }
        th { background: #eee; }
        h2, p { text-align: center; }
    </style>
</head>
<body>

    <h2>Sales Report</h2>
    <p>
        From {{ \Carbon\Carbon::parse(request()->fromDate ?? $orders->first()->created_at)->format('d/m/Y') }}
        to {{ \Carbon\Carbon::parse(request()->toDate ?? $orders->last()->created_at)->format('d/m/Y') }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Sr No</th>
                <th>Date</th>
                <th>Receipt No</th>
                <th>Party Name</th>
                <th>Party Phone</th>
                <th>Total Quantity</th>
                <th>Total Amount (incl. taxes)</th>
                <th>Created By</th>
            </tr>
        </thead>
        <tbody>
            @foreach($orders as $index => $order)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($order->created_at)->format('d-m-Y') }}</td>
                    <td>{{ $order->order_number ?? '-' }}</td>
                    <td>{{ $order->customer_name ?? ($order->table->name ?? 'N/A') }}</td>
                    <td>{{ $order->mobile ?? '-' }}</td>
                    <td>{{ $order->total_qty ?? 0 }}</td>
                    <td>₹{{ number_format($order->total_amount, 2) }}</td>
                    <td>{{ $order->user->name ?? 'Admin' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6" style="text-align: right;"><strong>Total</strong></td>
                <td colspan="2"><strong>₹{{ number_format($totalAmount, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
