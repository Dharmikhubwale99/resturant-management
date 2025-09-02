<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class WindowsBillController extends Controller
{
    /**
     * Windows (QZ) launcher view — opens QZ, selects default printer, fetches ESC/POS, prints.
     */
    public function launchBill(Order $order)
    {
        Log::info('WindowsBillController.launchBill', [
            'order_id' => $order->id,
            'user_id'  => auth()->id(),
        ]);

        return response()->view('prints.windows-bill-qz', [
            'orderId' => $order->id,
        ]);
    }

    /**
     * Returns raw ESC/POS text for the bill.
     * This mirrors your BluetoothBillController totals logic,
     * but renders fix-width text for thermal (80mm/58mm).
     */
    public function escposBill(Order $order)
    {
        // Eager load like BluetoothBillController
        $order->load([
            'orderItems',
            'orderItems.item.taxSetting',
            'orderItems.variant',
            'table',
            'restaurant',
            'restaurant.billPrintSetting',
        ]);

        // SETTINGS
        $cfg = optional($order->restaurant->billPrintSetting)->merged()
            ?? \App\Models\BillPrintSetting::defaults();

        // chars-per-line
        $cpl   = $cfg['cpl_override'] ?: ($cfg['paper'] === '80mm' ? 48 : 32); // text-ESC/POS માટે થોડું નવઘડું CPL
        $qtyW  = 4;  $rateW = 7;  $amtW = 8;  $gaps = 3;
        $nameW = max(8, $cpl - ($qtyW + $rateW + $amtW + $gaps));

        // helpers
        $padR = fn($s, $w) => mb_strimwidth($s, 0, $w, '', 'UTF-8') . str_repeat(' ', max(0, $w - mb_strwidth($s, 'UTF-8')));
        $padL = fn($s, $w) => str_repeat(' ', max(0, $w - mb_strwidth($s, 'UTF-8'))) . mb_strimwidth($s, 0, $w, '', 'UTF-8');

        $line  = function() use ($cpl) { return str_repeat('-', $cpl) . "\n"; };
        $center= function($txt) use ($cpl) {
            $len = mb_strwidth($txt, 'UTF-8');
            $pad = max(0, intdiv(($cpl - $len), 2));
            return str_repeat(' ', $pad) . $txt . "\n";
        };

        $buf = '';

        // ==== Header ====
        $name = strtoupper($order->restaurant->name ?? 'YOUR RESTAURANT');
        $buf .= $center($name);

        if (!empty($cfg['show_address']) && $order->restaurant?->address) {
            $addr = trim(($order->restaurant->address ?? '') . '  Ph: ' . ($order->restaurant->mobile ?? ''));
            $buf .= $center($addr);
        }
        if (!empty($cfg['show_gstin']) && $order->restaurant?->gstin) {
            $buf .= $center('GSTIN: ' . $order->restaurant->gstin);
        }
        $buf .= $line();

        // Meta
        $buf .= "Bill No: " . ($order->bill_number ?? '-') . "\n";
        if (!empty($cfg['show_order_no'])) {
            $buf .= "Order No: " . ($order->order_number ?? '-') . "\n";
        }
        $buf .= "Date: " . $order->created_at->format('d/m/Y h:i A') . "\n";

        if (!empty($cfg['show_table']) || !empty($cfg['show_order_type'])) {
            $meta = [];
            if (!empty($cfg['show_table']))      { $meta[] = 'Table: ' . ($order->table->name ?? 'N/A'); }
            if (!empty($cfg['show_order_type'])) { $meta[] = 'Type: ' . ucfirst($order->order_type); }
            $buf .= implode('   ', $meta) . "\n";
        }
        $buf .= $line();

        // Items header
        if (!empty($cfg['show_items_header'])) {
            $buf .=
                $padR('Item', $nameW) . ' ' .
                $padL('Qty', $qtyW) . ' ' .
                $padL('Rate', $rateW) . ' ' .
                $padL('Amt', $amtW) . "\n";
        }

        // ==== Items + Tax ====
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
            // wrap name into chunks width $nameW
            $chunks = [];
            $remaining = $name;
            while (mb_strwidth($remaining, 'UTF-8') > $nameW) {
                $chunks[]   = mb_strimwidth($remaining, 0, $nameW, '', 'UTF-8');
                $remaining  = mb_substr($remaining, mb_strlen($chunks[count($chunks)-1], 'UTF-8'), null, 'UTF-8');
                if ($remaining === '') break;
            }
            if ($remaining !== '') $chunks[] = $remaining;

            $qtyStr  = (string)$qty;
            $rateStr = number_format($base, 2);
            $amtStr  = number_format($lineBaseTotal, 2);

            // first line with qty/rate/amt
            $buf .=
                $padR($chunks[0], $nameW) . ' ' .
                $padL($qtyStr,  $qtyW)    . ' ' .
                $padL($rateStr, $rateW)   . ' ' .
                $padL($amtStr,  $amtW)    . "\n";

            // wrapped name only
            if (count($chunks) > 1) {
                for ($i=1; $i<count($chunks); $i++) {
                    $buf .= $padR($chunks[$i], $nameW) . "\n";
                }
            }

            if (!empty($cfg['show_item_notes']) && !empty($row->special_notes)) {
                $buf .= '  • ' . $row->special_notes . "\n";
            }
        }

        $grandTotal = $subTotal + $totalCgst + $totalSgst;

        if (!empty($cfg['round_grand_total'])) {
            $orig = $grandTotal;
            if (($cfg['round_mode'] ?? 'nearest') === 'up')       $grandTotal = ceil($grandTotal);
            elseif (($cfg['round_mode'] ?? 'nearest') === 'down') $grandTotal = floor($grandTotal);
            else                                                  $grandTotal = round($grandTotal);

            $diff = $grandTotal - $orig;
            if (abs($diff) >= 0.01) {
                $buf .= $line();
                $buf .= 'Round Off: ' . number_format($diff, 2) . "\n";
            }
        }

        $buf .= $line();

        // Totals
        $buf .= "Items: {$totalItems}    Qty: {$totalQty}\n";
        $buf .= $padL('Sub Total: ' . number_format($subTotal, 2), $cpl) . "\n";

        if (!empty($cfg['show_tax_breakup']) && ($totalCgst>0 || $totalSgst>0)) {
            $buf .= $padL('CGST: ' . number_format($totalCgst, 2), $cpl) . "\n";
            $buf .= $padL('SGST: ' . number_format($totalSgst, 2), $cpl) . "\n";
            $buf .= $padL('Tax Value: ' . number_format($totalCgst + $totalSgst, 2), $cpl) . "\n";
        }

        $buf .= $padL('Grand Total: ' . number_format($grandTotal, 2), $cpl) . "\n";
        $buf .= $line();

        if (!empty($cfg['show_footer_msg']) && !empty($cfg['footer_msg'])) {
            $buf .= $center($cfg['footer_msg']);
        }

        // ESC/POS: cut + few feeds (optional)
        $buf .= "\n\n";
        $buf .= "\x1D\x56\x00"; // GS V 0 — full cut (supported printers only)

        return response($buf, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }
}
