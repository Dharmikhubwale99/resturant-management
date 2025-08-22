<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Restaurant;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use App\Models\Setting;

class BluetoothBillController extends Controller
{
    public function billJson(Request $request, Order $order)
    {
        abort_unless($request->hasValidSignature(), 401);

        $userId     = (int) $request->query('uid');
        $userRestId = Restaurant::where('user_id', $userId)->value('id');
        abort_if((int)$order->restaurant_id !== (int)$userRestId, 403, 'Forbidden');

        Log::info('BluetoothBillController.billJson', [
            'order_id'      => $order->id,
            'signed_uid'    => $userId,
            'user_rest_id'  => $userRestId,
            'order_rest_id' => $order->restaurant_id,
        ]);

        $order->load(['orderItems', 'orderItems.item.taxSetting', 'orderItems.variant', 'table', 'restaurant', 'restaurant.billPrintSetting']);

        // --- SETTINGS ---
        $cfg = optional($order->restaurant->billPrintSetting)->merged()
            ?? \App\Models\BillPrintSetting::defaults();

        // helper
        $style = function(string $key, array $fallback = ['bold'=>0,'align'=>0,'format'=>0]) use ($cfg) {
            return $cfg['styles'][$key] ?? $fallback;
        };

        // CPL
        $cpl   = $cfg['cpl_override'] ?: ($cfg['paper'] === '80mm' ? 64 : 42);
        $qtyW  = 4;  $rateW = 7;  $amtW = 8;  $gaps = 3;
        $nameW = max(8, $cpl - ($qtyW + $rateW + $amtW + $gaps));

        $lines = [];

        // ===== Header =====
        $logoSrc = $this->toPublicUrl($order->restaurant?->logo_url);

        if (!$logoSrc) {
            // fetch your global settings; rename model/field if different in your app
            $siteSettings = Setting::query()->select('favicon')->first();
            $logoSrc = $this->toPublicUrl($siteSettings?->favicon);
        }

        if (!empty($cfg['show_logo']) && $logoSrc) {
            $lines[] = (object)[
                'type'    => 4,
                'content' => '<div style="text-align:center;margin-bottom:4px"><img src="' . e($logoSrc) . '" style="max-width:150px;max-height:80px"/></div>',
            ];
        }

        $st = $style('header_restaurant_name', ['bold'=>1,'align'=>1,'format'=>0]);
        $lines[] = (object)[
            'type'=>0,'content'=>strtoupper($order->restaurant->name ?? 'YOUR RESTAURANT'),
            'bold'=>$st['bold'],'align'=>$st['align'],'format'=>$st['format'],
        ];

        if (!empty($cfg['show_address']) && $order->restaurant?->address) {
            $st = $style('header_address', ['bold'=>0,'align'=>1,'format'=>$cfg['font_small_format']]);
            $lines[] = (object)[
                'type'=>0,
                'content'=> trim(($order->restaurant->address ?? '') . '  Ph: ' . ($order->restaurant->mobile ?? '')),
                'bold'=>$st['bold'],'align'=>$st['align'],'format'=>$st['format'],
            ];
        }

        if (!empty($cfg['show_gstin']) && $order->restaurant?->gstin) {
            $st = $style('header_gstin', ['bold'=>0,'align'=>1,'format'=>$cfg['font_small_format']]);
            $lines[] = (object)[
                'type'=>0,'content'=>'GSTIN: ' . $order->restaurant->gstin,
                'bold'=>$st['bold'],'align'=>$st['align'],'format'=>$st['format'],
            ];
        }

        $lines[] = (object)['type'=>4,'content'=>'<div style="border-top:1px dashed #000;margin:6px 0"></div>'];

        // Meta
        $st = $style('meta_bill_no', ['bold'=>1,'align'=>0,'format'=>0]);
        $lines[] = (object)['type'=>0,'content'=>'Bill No: ' . ($order->bill_number ?? '-'), 'bold'=>$st['bold'],'align'=>$st['align'],'format'=>$st['format']];

        if (!empty($cfg['show_order_no'])) {
            $st = $style('meta_order_no', ['bold'=>0,'align'=>0,'format'=>0]);
            $lines[] = (object)['type'=>0,'content'=>'Order No: ' . ($order->order_number ?? '-'), 'bold'=>$st['bold'],'align'=>$st['align'],'format'=>$st['format']];
        }

        $st = $style('meta_date', ['bold'=>0,'align'=>0,'format'=>0]);
        $lines[] = (object)['type'=>0,'content'=>'Date: ' . $order->created_at->format('d/m/Y h:i A'), 'bold'=>$st['bold'],'align'=>$st['align'],'format'=>$st['format']];

        if (!empty($cfg['show_table']) || !empty($cfg['show_order_type'])) {
            $meta = [];
            if (!empty($cfg['show_table']))      { $meta[] = 'Table: ' . ($order->table->name ?? 'N/A'); }
            if (!empty($cfg['show_order_type'])) { $meta[] = 'Type: ' . ucfirst($order->order_type); }
            $st = $style('meta_table_type', ['bold'=>1,'align'=>0,'format'=>0]);
            $lines[] = (object)['type'=>0,'content'=>implode('   ', $meta), 'bold'=>$st['bold'],'align'=>$st['align'],'format'=>$st['format']];
        }

        $lines[] = (object)['type'=>4,'content'=>'<div style="border-top:1px dashed #000;margin:6px 0"></div>'];

        if (!empty($cfg['show_items_header'])) {
            $st = $style('items_header', ['bold'=>1,'align'=>0,'format'=>$cfg['font_small_format']]);
            $lines[] = (object)[
                'type'=>0,
                'content'=> str_pad('Item', $nameW, ' ', STR_PAD_RIGHT) . ' ' .
                            str_pad('Qty',   $qtyW,  ' ', STR_PAD_LEFT)   . ' ' .
                            str_pad('Rate',  $rateW, ' ', STR_PAD_LEFT)   . ' ' .
                            str_pad('Amt',   $amtW,  ' ', STR_PAD_LEFT),
                'bold'=>$st['bold'],'align'=>$st['align'],'format'=>$st['format'],
            ];
        }

        // ===== Items + tax =====
        $subTotal=0.0; $totalCgst=0.0; $totalSgst=0.0; $totalQty=0; $totalItems=0;

        foreach ($order->orderItems as $row) {
            $qty        = (int) $row->quantity;
            $rateInput  = (float) $row->base_price;
            $tax        = optional($row->item->taxSetting);
            $taxRate    = $tax?->rate ?? 0;
            $isInclusive= (bool) ($row->item->is_tax_inclusive ?? false);

            if ($taxRate > 0) {
                if ($isInclusive) {
                    $base       = $rateInput - ($rateInput * $taxRate) / 100;
                    $taxPerUnit = ($rateInput * $taxRate) / 100;
                } else {
                    $base       = $rateInput;
                    $taxPerUnit = ($base * $taxRate) / 100;
                }
            } else {
                $base = $rateInput; $taxPerUnit = 0.0;
            }

            $lineBaseTotal = $base * $qty;

            $subTotal  += $lineBaseTotal;
            $totalCgst += ($taxPerUnit / 2) * $qty;
            $totalSgst += ($taxPerUnit / 2) * $qty;
            $totalQty  += $qty;
            $totalItems++;

            $name   = trim(($row->item->name ?? 'Item') . ($row->variant ? ' ('.$row->variant->name.')' : ''));
            $qtyStr = (string)$qty;
            $rateStr= number_format($base, 2);
            $amtStr = number_format($lineBaseTotal, 2);

            $chunks = mb_str_split($name, $nameW);

            $st = $style('item_name_line', ['bold'=>0,'align'=>0,'format'=>$cfg['font_small_format']]);
            $firstLine =
                str_pad($chunks[0], $nameW, ' ', STR_PAD_RIGHT) . ' ' .
                str_pad($qtyStr,  $qtyW,  ' ', STR_PAD_LEFT)    . ' ' .
                str_pad($rateStr, $rateW, ' ', STR_PAD_LEFT)    . ' ' .
                str_pad($amtStr,  $amtW,  ' ', STR_PAD_LEFT);

            $lines[] = (object)[
                'type'=>0,'content'=>$firstLine,
                'bold'=>$st['bold'],'align'=>$st['align'],'format'=>$st['format'],
            ];

            if (count($chunks) > 1) {
                $stWrap = $style('item_name_wrap', ['bold'=>0,'align'=>0,'format'=>$cfg['font_small_format']]);
                for ($i=1; $i<count($chunks); $i++) {
                    $lines[] = (object)[
                        'type'=>0,'content'=>str_pad($chunks[$i], $nameW, ' ', STR_PAD_RIGHT),
                        'bold'=>$stWrap['bold'],'align'=>$stWrap['align'],'format'=>$stWrap['format'],
                    ];
                }
            }

            if (!empty($cfg['show_item_notes']) && !empty($row->special_notes)) {
                $stNotes = $style('item_notes', ['bold'=>0,'align'=>0,'format'=>$cfg['font_small_format']]);
                $lines[] = (object)[
                    'type'=>0,'content'=>'  • '.$row->special_notes,
                    'bold'=>$stNotes['bold'],'align'=>$stNotes['align'],'format'=>$stNotes['format'],
                ];
            }
        }

        $grandTotal = $subTotal + $totalCgst + $totalSgst;

        if (!empty($cfg['round_grand_total'])) {
            $orig = $grandTotal;
            if ($cfg['round_mode'] === 'up')       $grandTotal = ceil($grandTotal);
            elseif ($cfg['round_mode'] === 'down') $grandTotal = floor($grandTotal);
            else                                    $grandTotal = round($grandTotal);

            $diff = $grandTotal - $orig;
            if (abs($diff) >= 0.01) {
                $lines[] = (object)['type'=>4,'content'=>'<div style="border-top:1px dashed #000;margin:6px 0"></div>'];
                $st = $style('round_off', ['bold'=>0,'align'=>2,'format'=>0]);
                $lines[] = (object)['type'=>0,'content'=>'Round Off: '.number_format($diff,2),'bold'=>$st['bold'],'align'=>$st['align'],'format'=>$st['format']];
            }
        }

        $lines[] = (object)['type'=>4,'content'=>'<div style="border-top:1px dashed #000;margin:6px 0"></div>'];

        // Totals block
        $st = $style('totals_row', ['bold'=>0,'align'=>0,'format'=>$cfg['font_small_format']]);
        $lines[] = (object)['type'=>0,'content'=>"Items: $totalItems    Qty: $totalQty",'bold'=>$st['bold'],'align'=>$st['align'],'format'=>$st['format']];

        $st = $style('sub_total', ['bold'=>0,'align'=>2,'format'=>0]);
        $lines[] = (object)['type'=>0,'content'=>'Sub Total: ' . number_format($subTotal, 2),'bold'=>$st['bold'],'align'=>$st['align'],'format'=>$st['format']];

        if (!empty($cfg['show_tax_breakup']) && ($totalCgst>0 || $totalSgst>0)) {
            $st = $style('cgst', ['bold'=>0,'align'=>2,'format'=>0]);
            $lines[] = (object)['type'=>0,'content'=>'CGST: ' . number_format($totalCgst, 2),'bold'=>$st['bold'],'align'=>$st['align'],'format'=>$st['format']];
            $st = $style('sgst', ['bold'=>0,'align'=>2,'format'=>0]);
            $lines[] = (object)['type'=>0,'content'=>'SGST: ' . number_format($totalSgst, 2),'bold'=>$st['bold'],'align'=>$st['align'],'format'=>$st['format']];
            $st = $style('tax_value', ['bold'=>0,'align'=>2,'format'=>0]);
            $lines[] = (object)['type'=>0,'content'=>'Tax Value: ' . number_format($totalCgst + $totalSgst, 2),'bold'=>$st['bold'],'align'=>$st['align'],'format'=>$st['format']];
        }

        $st = $style('grand_total', ['bold'=>1,'align'=>2,'format'=>0]);
        $lines[] = (object)['type'=>0,'content'=>'Grand Total: ' . number_format($grandTotal, 2),'bold'=>$st['bold'],'align'=>$st['align'],'format'=>$st['format']];

        // if (!empty($cfg['show_payment']) && !empty($order->payment_method)) {
        //     $st = $style('payment', ['bold'=>0,'align'=>2,'format'=>$cfg['font_small_format']]);
        //     $lines[] = (object)['type'=>0,'content'=>'Payment: ' . strtoupper($order->payment_method),'bold'=>$st['bold'],'align'=>$st['align'],'format'=>$st['format']];
        // }

        $lines[] = (object)['type'=>4,'content'=>'<div style="border-top:1px dashed #000;margin:6px 0"></div>'];
        if (!empty($cfg['show_footer_msg']) && !empty($cfg['footer_msg'])) {
            $st = $style('footer_msg', ['bold'=>1,'align'=>1,'format'=>0]);
            $lines[] = (object)['type'=>0,'content'=>$cfg['footer_msg'],'bold'=>$st['bold'],'align'=>$st['align'],'format'=>$st['format']];
        }

        // Return clean JSON ARRAY (not force-object)
        return response()->json($lines, 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function launchBill(Order $order)
    {
        $responseUrl = URL::temporarySignedRoute(
            'bt.bill.response',
            now()->addMinutes(3),
            ['order' => $order->id, 'uid' => auth()->id()]
        );

        Log::info('BluetoothBillController.launchBill', [
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'response_url' => $responseUrl,
        ]);

        return response()->view('prints.launch-btbill', ['responseUrl' => $responseUrl]);
    }
}
