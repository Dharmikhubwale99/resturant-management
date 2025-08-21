<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Restaurant;
use Illuminate\Support\Facades\URL;

class BluetoothBillController extends Controller
{
    /**
     * RESPONSE URL → JSON that the Thermer app will print.
     * IMPORTANT: Clean JSON only (no whitespace/extra text).
     */
    public function billJson(Request $request, Order $order)
    {
        // --- Auth via signed URL ---
        abort_unless($request->hasValidSignature(), 401);

        $userId = (int) $request->query('uid');
        $userRestId = Restaurant::where('user_id', $userId)->value('id');

        abort_if((int) $order->restaurant_id !== (int) $userRestId, 403, 'Forbidden');

        Log::info('BluetoothBillController.billJson', [
            'order_id'      => $order->id,
            'signed_uid'    => $userId,
            'user_rest_id'  => $userRestId,
            'order_rest_id' => $order->restaurant_id,
        ]);

        $order->load(['orderItems', 'orderItems.item.taxSetting', 'orderItems.variant', 'table', 'restaurant']);

        $lines = [];

        // ===== Header =====
        $lines[] = (object)[
            'type'   => 0,
            'content'=> strtoupper($order->restaurant->name ?? 'YOUR RESTAURANT'),
            'bold'   => 1,
            'align'  => 1,
            'format' => 0,
        ];
        if ($order->restaurant?->address) {
            $lines[] = (object)[
                'type'   => 0,
                'content'=> trim(($order->restaurant->address ?? '') . '  Ph: ' . ($order->restaurant->mobile ?? '')),
                'bold'   => 0,
                'align'  => 1,
                'format' => 4, // small font
            ];
        }
        if ($order->restaurant?->gstin) {
            $lines[] = (object)[
                'type'   => 0,
                'content'=> 'GSTIN: ' . $order->restaurant->gstin,
                'bold'   => 0,
                'align'  => 1,
                'format' => 4,
            ];
        }

        // separator
        $lines[] = (object)[
            'type'    => 4, // HTML
            'content' => '<div style="border-top:1px dashed #000;margin:6px 0"></div>',
        ];

        // Bill meta
        $lines[] = (object)['type'=>0,'content'=>'Bill No: ' . ($order->bill_number ?? '-'), 'bold'=>1,'align'=>0,'format'=>0];
        $lines[] = (object)['type'=>0,'content'=>'Order No: ' . ($order->order_number ?? '-'), 'bold'=>0,'align'=>0,'format'=>0];
        $lines[] = (object)['type'=>0,'content'=>'Date: ' . $order->created_at->format('d/m/Y h:i A'), 'bold'=>0,'align'=>0,'format'=>0];
        $lines[] = (object)['type'=>0,'content'=>'Table: ' . ($order->table->name ?? 'N/A') . '   Type: ' . ucfirst($order->order_type), 'bold'=>1,'align'=>0,'format'=>0];

        // separator
        $lines[] = (object)[
            'type'    => 4,
            'content' => '<div style="border-top:1px dashed #000;margin:6px 0"></div>',
        ];

        // Items header (55mm small font ≈ 42 chars/line)
        $lines[] = (object)[
            'type'   => 0,
            'content'=> 'Item                  Qty   Rate    Amt',
            'bold'   => 1,
            'align'  => 0,
            'format' => 4,
        ];

        // ===== Items + tax calc =====
        $subTotal   = 0.0;
        $totalCgst  = 0.0;
        $totalSgst  = 0.0;
        $totalQty   = 0;
        $totalItems = 0;

        foreach ($order->orderItems as $row) {
            $qty        = (int) $row->quantity;
            $rateInput  = (float) $row->base_price; // entered price
            $tax        = optional($row->item->taxSetting);
            $taxRate    = $tax?->rate ?? 0;         // e.g., 5
            $isInclusive= (bool) ($row->item->is_tax_inclusive ?? false);

            // Normalize to base exclusive
            if ($taxRate > 0) {
                if ($isInclusive) {
                    $base = $rateInput - ($rateInput * $taxRate) / 100; // price without tax
                    $taxPerUnit = ($rateInput * $taxRate) / 100;
                } else {
                    $base = $rateInput;
                    $taxPerUnit = ($base * $taxRate) / 100;
                }
            } else {
                $base = $rateInput;
                $taxPerUnit = 0.0;
            }

            $lineBaseTotal = $base * $qty;

            $subTotal  += $lineBaseTotal;
            $totalCgst += ($taxPerUnit / 2) * $qty;
            $totalSgst += ($taxPerUnit / 2) * $qty;
            $totalQty  += $qty;
            $totalItems++;

            // ---- one-line item row for 55mm small font ----
            // Plan (CPL ≈ 42): nameW + ' ' + qtyW + ' ' + rateW + ' ' + amtW = 42
            $cpl   = 42;
            $qtyW  = 4;  // e.g. x12
            $rateW = 7;  // 999.99
            $amtW  = 8;  // 9999.99
            $gaps  = 3;  // three single-space gaps
            $nameW = $cpl - ($qtyW + $rateW + $amtW + $gaps);
            if ($nameW < 8) { $nameW = 8; }

            $name = trim(($row->item->name ?? 'Item') . ($row->variant ? ' (' . $row->variant->name . ')' : ''));

            $qtyStr  = $qty;
            $rateStr = number_format($base, 2);
            $amtStr  = number_format($lineBaseTotal, 2);
            $nameFit = mb_strimwidth($name, 0, $nameW, '');

            $chunks = mb_str_split($name, $nameW);

            // first line → name chunk + qty/rate/amt
            $firstLine =
                str_pad($chunks[0], $nameW, ' ', STR_PAD_RIGHT) . ' ' .
                str_pad($qtyStr,  $qtyW,  ' ', STR_PAD_LEFT)    . ' ' .
                str_pad($rateStr, $rateW, ' ', STR_PAD_LEFT)    . ' ' .
                str_pad($amtStr,  $amtW,  ' ', STR_PAD_LEFT);

            $lines[] = (object)[
                'type'   => 0,
                'content'=> $firstLine,
                'bold'   => 0,
                'align'  => 0,
                'format' => 4, // small for 42 CPL
            ];

            if (count($chunks) > 1) {
                for ($i = 1; $i < count($chunks); $i++) {
                    $lines[] = (object)[
                        'type'   => 0,
                        'content'=> str_pad($chunks[$i], $nameW, ' ', STR_PAD_RIGHT),
                        'bold'   => 0,
                        'align'  => 0,
                        'format' => 4,
                    ];
                }
            }

            // Optional: special notes on next indented line
            if (!empty($row->special_notes)) {
                $lines[] = (object)[
                    'type'   => 0,
                    'content'=> '  • ' . $row->special_notes,
                    'bold'   => 0,
                    'align'  => 0,
                    'format' => 4,
                ];
            }
        }

        $grandTotal = $subTotal + $totalCgst + $totalSgst;

        // separator
        $lines[] = (object)[
            'type'    => 4,
            'content' => '<div style="border-top:1px dashed #000;margin:6px 0"></div>',
        ];

        // Totals
        $lines[] = (object)['type'=>0, 'content'=>"Items: $totalItems    Qty: $totalQty", 'bold'=>0,'align'=>0,'format'=>4];
        $lines[] = (object)['type'=>0, 'content'=>'Sub Total: ' . number_format($subTotal, 2), 'bold'=>0,'align'=>2,'format'=>0];

        if ($totalCgst > 0 || $totalSgst > 0) {
            $lines[] = (object)['type'=>0, 'content'=>'CGST: ' . number_format($totalCgst, 2), 'bold'=>0,'align'=>2,'format'=>0];
            $lines[] = (object)['type'=>0, 'content'=>'SGST: ' . number_format($totalSgst, 2), 'bold'=>0,'align'=>2,'format'=>0];
            $lines[] = (object)['type'=>0, 'content'=>'Tax Value: ' . number_format($totalCgst + $totalSgst, 2), 'bold'=>0,'align'=>2,'format'=>0];
        }

        $lines[] = (object)['type'=>0, 'content'=>'Grand Total: ' . number_format($grandTotal, 2), 'bold'=>1,'align'=>2,'format'=>0];

        if (!empty($order->payment_method)) {
            $lines[] = (object)['type'=>0, 'content'=>'Payment: ' . strtoupper($order->payment_method), 'bold'=>0,'align'=>2,'format'=>4];
        }
        if (isset($order->balance_amount)) {
            $lines[] = (object)['type'=>0, 'content'=>'Balance: ' . number_format((float) $order->balance_amount, 2), 'bold'=>0,'align'=>2,'format'=>0];
        }

        // Footer
        $lines[] = (object)[
            'type'    => 4,
            'content' => '<div style="border-top:1px dashed #000;margin:6px 0"></div>',
        ];
        $lines[] = (object)['type'=>0, 'content'=>'— Thank You! Visit Again —', 'bold'=>1, 'align'=>1, 'format'=>0];

        // --- Return clean JSON object ---
        return response()->json($lines, 200, [], JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * LAUNCH PAGE → opens my.bluetoothprint.scheme://<RESPONSEURL>
     * Same as KOT launch, just points to bill response.
     */
    public function launchBill(Order $order)
    {
        $responseUrl = URL::temporarySignedRoute(
            'bt.bill.response',
            now()->addMinutes(3), // short expiry
            [
                'order' => $order->id,
                'uid' => auth()->id(), // pass current user id
            ],
        );
        Log::info('BluetoothBillController.launchBill', [
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'response_url' => $responseUrl,
        ]);

        return response()->view('prints.launch-btbill', [
            'responseUrl' => $responseUrl,
        ]);
    }
}
