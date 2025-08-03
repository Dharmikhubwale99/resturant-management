<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Item Sale Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size: 12px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 6px;
            text-align: center;
        }

        th {
            background: #eee;
        }

        h2,
        p {
            text-align: center;
        }
    </style>
</head>

<body>

    <h2><strong>Item Sale Report</strong></h2>
    <p>
        From {{ \Carbon\Carbon::parse($fromDate)->format('d/m/Y') }}
        to {{ \Carbon\Carbon::parse($toDate)->format('d/m/Y') }}
    </p>

    <table>
        <thead>
            <tr>
                <th>Sr No</th>
                <th>Item Name</th>
                <th>Category</th>
                <th>Total Sale Quantity</th>
                <th>Total Sale Amount</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalQty = 0;
                $totalAmount = 0;
            @endphp
            @foreach ($data as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ $item->category_name ?? 'N/A' }}</td>
                    <td>{{ $item->total_qty }}</td>
                    <td>₹{{ number_format($item->total_amount, 2) }}</td>
                </tr>
                @php
                    $totalQty += $item->total_qty;
                    $totalAmount += $item->total_amount;
                @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="3" style="text-align: right;"><strong>Total</strong></td>
                <td><strong>{{ $totalQty }}</strong></td>
                <td><strong>₹{{ number_format($totalAmount, 2) }}</strong></td>
            </tr>
        </tfoot>
    </table>

</body>

</html>
