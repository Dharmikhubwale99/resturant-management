<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bill #{{ $order->id }}</title>
    <style>
        @media print {
            @page {
                size: 58mm auto;
                margin: 0;
            }

            html, body {
                margin: 0 !important;
                padding: 0 !important;
                font-family: monospace;
                background: white;
            }

            body * {
                visibility: hidden;
            }

            .print-area, .print-area * {
                visibility: visible;
            }

            .print-area {
                position: absolute;
                left: 0;
                top: 0;
            }
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

        table {
            width: 100%;
            font-size: 12px;
            border-collapse: collapse;
        }

        th, td {
            padding: 2px 0;
        }

        hr {
            border: none;
            border-top: 1px dashed black;
            margin: 4px 0;
        }
    </style>
</head>
<body>
    <div class="print-area w-[58mm] mx-auto px-2 py-1 text-[12px] leading-tight font-mono">

        <!-- RESTAURANT INFO -->
        <div class="text-center mb-1">
            <h2 class="font-bold text-[14px] uppercase">
                {{ $restaurant->name ?? 'Your Restaurant' }}
            </h2>
            <p class="text-[11px] leading-tight">
                {{ $restaurant->address ?? '' }}<br>
                Phone: {{ $restaurant->mobile ?? '-' }}<br>
                Email: {{ $restaurant->email ?? '-' }}
                @if ($restaurant->gstin)
                    <br>FSSAI No.: {{ $restaurant->gstin }}
                @endif
            </p>
        </div>

        <hr>

        <!-- BILL META -->
        <div>
            <p><strong>Order No:</strong> {{ $order->order_number }}</p>
            <p><strong>Created On:</strong> {{ $order->created_at->format('d/m/y h:i A') }}</p>
            <p><strong>Bill To:</strong> {{ ucfirst($order->order_type) }}</p>
        </div>

        <hr>

        <!-- ITEM TABLE -->
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
                    $totalItems = count($order->orderItems);
                @endphp

                @foreach ($order->orderItems as $item)
                    @php
                        $qty = $item->quantity;
                        $rate = $item->base_price;
                        $lineTotal = $item->total_price;
                        $totalQty += $qty;
                    @endphp
                    <tr>
                        <td>
                            {{ $item->item->name }}
                            @if ($item->variant)
                                <small>({{ $item->variant->name }})</small>
                            @endif
                        </td>
                        <td class="text-center">{{ $qty }}</td>
                        <td class="text-right">{{ number_format($rate, 0) }}</td>
                        <td class="text-right">{{ number_format($lineTotal, 0) }}</td>
                    </tr>
                    @if ($item->special_notes)
                        <tr>
                            <td colspan="4" class="text-[10px] italic text-gray-600 pl-1">
                                [Note] {{ $item->special_notes }}
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>

        <hr>

        <!-- SUMMARY -->
        <table>
            <tr>
                <td>Total Items: {{ $totalItems }}</td>
                <td colspan="3" class="text-right">Total Qty: {{ $totalQty }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right">Sub Total:</td>
                <td class="text-right">{{ number_format($order->total_amount, 0) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right font-bold">Total:</td>
                <td class="text-right font-bold">{{ number_format($order->total_amount, 0) }}</td>
            </tr>
            <tr>
                <td colspan="3" class="text-right">Balance:</td>
                <td class="text-right">{{ number_format($order->total_amount, 0) }}</td>
            </tr>
        </table>

        <hr>

        <!-- FOOTER -->
        <div class="text-center text-[10px] mt-2">
            Thank You! Visit Again 🙏
        </div>
    </div>

    <script>
        window.onload = () => window.print();
    </script>
</body>
</html>
