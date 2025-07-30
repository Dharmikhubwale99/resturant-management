<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Expense Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: center; }
        th { background: #eee; }
        h2, p { text-align: center; }
    </style>
</head>
<body>

    <h2><strong>Expense Report</strong></h2>
    <p>
        From {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }}
        to {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Sr No</th>
                <th>Date</th>
                <th>Party Name</th>
                <th>Expense Type</th>
                <th>Amount Paid</th>
            </tr>
        </thead>
        <tbody>
            @foreach($expenses as $index => $expense)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($expense->paid_at)->format('d-m-Y') }}</td>
                    <td>{{ $expense->name ?? '-' }}</td>
                    <td>{{ $expense->expenseType->name ?? '-' }}</td>
                    <td>₹{{ number_format($expense->amount, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" style="text-align: right;"><strong>Total</strong></td>
                <td><strong>₹{{ number_format($totalAmount, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

</body>
</html>
