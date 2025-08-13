<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Bill #{{ $order->bill_number }}</title>
    <style>
        @media print {
            @page {
                size: 57mm auto;
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
        <p><strong>Table No:</strong> {{ $order->table->name }}</p>
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
                    $totalTaxable = 0; // will equal $subTotal below
                    $totalCgst = 0;
                    $totalSgst = 0;
                    $subTotal = 0;
                @endphp


                @foreach ($order->orderItems as $item)
                @php
                $qty = $item->quantity;
                $rate = $item->base_price;            // per-unit entered price
                $variant = $item->variant;
                $tax = optional($item->item->taxSetting);
                $taxRate = $tax?->rate ?? 0;          // e.g., 5
                $isInclusive = $item->item->is_tax_inclusive;

                // We will DISPLAY base (tax-exclusive) price in Rate column
                if ($taxRate > 0) {
                    if ($isInclusive) {
                        // YOUR SPEC: inclusive=100 should SHOW 95 and tax 5
                        $basePrice = $rate - ($rate * $taxRate / 100);  // 100 - 5 = 95
                        $taxPerUnit = ($rate * $taxRate / 100);         // 5
                    } else {
                        // exclusive=100 should SHOW 100 and tax 5
                        $basePrice = $rate;                              // 100
                        $taxPerUnit = ($basePrice * $taxRate / 100);     // 5
                    }
                } else {
                    $basePrice = $rate;
                    $taxPerUnit = 0;
                }

                $cgstPerUnit = $taxPerUnit / 2;
                $sgstPerUnit = $taxPerUnit / 2;

                // Totals (base subtotal + split taxes)
                $lineBaseTotal = $basePrice * $qty;
                $subTotal     += $lineBaseTotal;
                $totalTaxable += $lineBaseTotal;     // same as subtotal for display
                $totalCgst    += $cgstPerUnit * $qty;
                $totalSgst    += $sgstPerUnit * $qty;

                $displayRate = $basePrice;           // show base in Rate column
                $lineTotal   = $lineBaseTotal;       // show base in Total column

                $totalQty += $qty;
                $totalItems++;
            @endphp
            @php
            $grandTotal = $subTotal + $totalCgst + $totalSgst;
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

            <tr>
                <td colspan="3" class="text-right">Sub Total:</td>
                <td class="text-right">{{ number_format($subTotal, 2) }}</td>
            </tr>

            @if ($totalCgst > 0 || $totalSgst > 0)
                <tr>
                    <td colspan="3" class="text-right">CGST:</td>
                    <td class="text-right">{{ number_format($totalCgst, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right">SGST:</td>
                    <td class="text-right">{{ number_format($totalSgst, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="3" class="text-right">Tax Value:</td>
                    <td class="text-right">{{ number_format($totalCgst + $totalSgst, 2) }}</td>
                </tr>
            @endif

            <tr>
                <td colspan="3" class="text-right font-bold">Grand Total:</td>
                <td class="text-right font-bold">{{ number_format($grandTotal, 2) }}</td>
            </tr>

            <tr>
                <td colspan="3" class="text-right">Balance:</td>
                <td class="text-right">{{ number_format($grandTotal, 2) }}</td>
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
