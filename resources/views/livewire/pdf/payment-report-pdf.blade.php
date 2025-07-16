<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #333; padding: 8px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>
    <h2>Payment Report</h2>
    <table>
        <thead>
            <tr>
                <th>Payment No</th>
                <th>Date</th>
                <th>Amount</th>
                <th>Method</th>
            </tr>
        </thead>
        <tbody>
            @foreach($payments as $row)
                <tr>
                    <td>{{ $row->id }}</td>
                    <td>{{ \Carbon\Carbon::parse($row->created_at)->format('d-m-Y') }}</td>
                    <td>₹{{ number_format($row->amount, 2) }}</td>
                    <td>{{ ucfirst($row->method) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align:right;"><strong>Total</strong></td>
                <td><strong>₹{{ number_format($totalAmount, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
