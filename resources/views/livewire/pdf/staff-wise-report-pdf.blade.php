<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Staff Wise Sales Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; }
        h2, p { text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
        th, td { border: 1px solid #333; padding: 6px; text-align: center; }
        th { background: #eee; }
    </style>
</head>
<body>

    <h2>Staff Wise Sales Report</h2>
    <p>
        From {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }}
        to {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}
    </p>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Staff Name</th>
                <th>Phone</th>
                <th>Total Orders</th>
                <th>Total Sales (₹)</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($staffList as $index => $staff)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $staff->name }}</td>
                    <td>{{ $staff->mobile ?? '-' }}</td>
                    <td>{{ $staff->total_orders ?? 0 }}</td>
                    <td>₹{{ number_format($staff->total_sales ?? 0, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

</body>
</html>
