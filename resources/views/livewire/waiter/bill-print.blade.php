<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bill #{{ $order->bill_number }}</title>
    <style>
        @media print {
            @page {
                size: 58mm auto;
                margin: 0;
            }

            html,
            body {
                margin: 0 !important;
                padding: 0 !important;
                font-family: monospace;
                background: white;
            }

            body * {
                visibility: hidden;
            }

            .print-area,
            .print-area * {
                visibility: visible;
            }

            .print-area {
                position: absolute;
                left: 0;
                top: 0;
            }
        }

        table {
            width: 100%;
            font-size: 12px;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 2px 0;
        }

        hr {
            border: none;
            border-top: 1px dashed black;
            margin: 4px 0;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="print-area w-[58mm] mx-auto px-2 py-1 text-[12px] leading-tight font-mono">
        <div class="text-center mb-1">
            <h2 class="font-bold text-[14px] uppercase">{{ $restaurant->name ?? 'Your Restaurant' }}</h2>
            <p class="text-[11px] leading-tight">
                {{ $restaurant->address }}<br>
                Phone: {{ $restaurant->mobile }}<br>
                Email: {{ $restaurant->email }}
                @if ($restaurant->gstin)
                    <br>FSSAI No.: {{ $restaurant->gstin }}
                @endif
                GST No: {{ $restaurant->gstin }}
            </p>
        </div>

        <hr>

        <p><strong>Bill No:</strong> {{ $order->bill_number }}</p>
        <p><strong>Order No:</strong> {{ $order->order_number }}</p>
        <p><strong>Date On:</strong> {{ $order->created_at->format('d/m/y h:i A') }}</p>
        <p><strong>Table No:</strong> {{ ucfirst($order->table_id) }}</p>
        <p><strong>Bill To:</strong> {{ ucfirst($order->order_type) }}</p>

        <hr>

        <table>
            <thead>
                <tr>
                    <th class="text-left">Item</th>
                    <th class="text-center">Qty</th>
                    <th class="text-right">Rate</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalQty = 0;
                    $totalItems = 0;
                    $totalTaxable = 0;
                    $totalCgst = 0;
                    $totalSgst = 0;
                @endphp

                @foreach ($order->orderItems as $item)
                    @php
                        $qty = $item->quantity;
                        $rate = $item->base_price;
                        $variant = $item->variant;
                        $tax = optional($item->item->taxSetting);
                        $taxRate = $tax?->rate ?? 0;
                        $isInclusive = $item->item->is_tax_inclusive;
                        $basePrice = $rate;
                        $cgst = $sgst = 0;

                        if ($taxRate > 0) {
                            if ($isInclusive) {
                                $basePrice = $rate / (1 + $taxRate / 100);
                            }
                            $taxAmount = $basePrice * ($taxRate / 100);
                            $cgst = $sgst = $taxAmount / 2;
                            $totalTaxable += $basePrice * $qty;
                            $totalCgst += $cgst * $qty;
                            $totalSgst += $sgst * $qty;
                        }

                        $lineTotal = $rate * $qty;
                        $totalQty += $qty;
                        $totalItems++;
                    @endphp

                    <tr>
                        <td>{{ $item->item->name }} @if ($variant)
                                ({{ $variant->name }})
                            @endif
                        </td>
                        <td class="text-right">{{ $qty }}</td>
                        <td class="text-right">{{ number_format($rate, 0) }}</td>
                        <td class="text-right">{{ number_format($lineTotal, 0) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <hr>

        <table>
            <tr>
                <td>Total Items: {{ $totalItems }}</td>
                <td colspan="3" class="text-right">Total Qty: {{ $totalQty }}</td>
            </tr>

            @if ($totalCgst > 0 || $totalSgst > 0)
                <tr>
                    <td colspan="3" class="text-right">Taxable Amt:</td>
                    <td class="text-right">{{ number_format($totalTaxable, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right">CGST:</td>
                    <td class="text-right">{{ number_format($totalCgst, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right">SGST:</td>
                    <td class="text-right">{{ number_format($totalSgst, 2) }}</td>
                </tr>
            @endif

            <tr>
                <td colspan="3" class="text-right font-bold">Grand Total:</td>
                <td class="text-right font-bold">{{ number_format($order->total_amount, 2) }}</td>
            </tr>

            <tr>
                <td colspan="3" class="text-right">Balance:</td>
                <td class="text-right">{{ number_format($order->total_amount, 2) }}</td>
            </tr>
        </table>

        <hr>

        <div class="text-center text-[10px] mt-2">
            Thank You! Visit Again 🙏
        </div>
    </div>

    <script>
        window.onload = () => window.print();
    </script>
</body>

</html>
