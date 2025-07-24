<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $order->id }}</title>
    <style>
        * {
            font-family: 'Segoe UI', sans-serif;
        }

        @media print {
            @page {
                size: A4;
                margin: 20mm;
            }
            footer, .hide-on-print {
                display: none !important;
            }
        }

        body {
            margin: 0;
            padding: 20px;
            background: #fff;
            color: #000;
        }

        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 24px;
            margin: 0;
        }

        .meta {
            display: flex;
            justify-content: space-between;
            margin-top: 10px;
            font-size: 14px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
            margin-top: 20px;
        }

        th, td {
            padding: 10px;
            border: 1px solid #999;
        }

        th {
            background: #f0f0f0;
        }

        .text-right {
            text-align: right;
        }

        .total {
            font-weight: bold;
            font-size: 16px;
            margin-top: 20px;
        }

    </style>
</head>
<body>
    <div class="invoice-box">
        <div class="header">
            <h1>Invoice #{{ $order->id }}</h1>
        </div>

        <div class="meta">
            <div><strong>Table:</strong> {{ $order->table->name ?? 'N/A' }}</div>
            <div><strong>Date:</strong> {{ $order->created_at->format('d-m-Y H:i') }}</div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Rate</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->orderItems as $item)
                    <tr>
                        <td>
                            {{ $item->item->name }}{{ $item->variant?->name ? ' (' . $item->variant->name . ')' : '' }}
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">₹{{ number_format($item->base_price, 2) }}</td>
                        <td class="text-right">₹{{ number_format($item->total_price, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="text-right total">
            Total: ₹{{ number_format($order->total_amount, 2) }}
        </div>
    </div>

    <script>
        window.onload = () => window.print();
    </script>
</body>
</html>
