<?php

namespace App\Services;

use App\Models\Order;
use Mike42\Escpos\Printer;
use Mike42\Escpos\CapabilityProfile;
use Mike42\Escpos\PrintConnectors\NetworkPrintConnector;
use Mike42\Escpos\PrintConnectors\CupsPrintConnector;
use Throwable;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;

class PosPrinter
{
    private function makePrinter(): Printer
    {
        $profile = CapabilityProfile::load(config('escpos.profile', 'default'));
        $driver  = config('escpos.driver', 'network');

        if ($driver === 'cups') {
            $dest = config('escpos.printer_name');
            if (!$dest) {
                throw new \RuntimeException('CUPS printer_name missing in config.');
            }
            $connector = new CupsPrintConnector($dest);
            Log::info('ESC/POS: using MAC connector with queue: '.$dest.' ('.get_class($connector).')');
            return new Printer($connector, $profile);
        }

        if ($driver === 'windows') {
            $dest = config('escpos.printer_name'); // e.g. "POS-58" (Control Panel → Printers display name)
            if (!$dest) {
                throw new \RuntimeException('Windows printer_name missing in config.');
            }
            $connector = new WindowsPrintConnector($dest);
            Log::info('ESC/POS: using Windows connector with printer: '.$dest.' ('.get_class($connector).')');
            return new Printer($connector, $profile);
        }

        // network (AppSocket/JetDirect :9100)
        $host = config('escpos.host');
        $port = (int) config('escpos.port', 9100);
        $timeout = (int) config('escpos.timeout', 30);

        if (empty($host)) {
            throw new \RuntimeException('Network host missing in config.');
        }
        $connector = new NetworkPrintConnector($host, $port, $timeout);
        Log::info("ESC/POS: using Network connector {$host}:{$port}, timeout={$timeout}s");
        return new Printer($connector, $profile);
    }

    public function printOrder(Order $order, bool $cutAndPulse = true): void
    {
        $printer = null; // ✅ init here so we can always close it

        try {
            $printer = $this->makePrinter();
            $cols = (int) config('escpos.width_cols', 32);

            $order->loadMissing(['restaurant','table','orderItems','orderItems.item.taxSetting','orderItems.variant']);

            // --- Header ---
            $printer->initialize();
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->selectPrintMode(Printer::MODE_DOUBLE_WIDTH);
            $printer->text(Str::upper($order->restaurant->name ?? 'RESTAURANT') . "\n");
            $printer->selectPrintMode();
            $printer->text(($order->restaurant->address ?? '') . "\n");
            $printer->text("Ph: " . ($order->restaurant->mobile ?? '-') . "\n");
            if (!empty($order->restaurant->email)) {
                $printer->text("Email: " . $order->restaurant->email . "\n");
            }
            if (!empty($order->restaurant->gstin)) {
                $printer->text("GST: " . $order->restaurant->gstin . "\n");
            }
            $printer->text(str_repeat('-', $cols) . "\n");

            $printer->setJustification(Printer::JUSTIFY_LEFT);
            $printer->text($this->lr("Bill No:", (string) ($order->bill_number ?? '-'), $cols));
            $printer->text($this->lr("Order No:", (string) ($order->order_number ?? '-'), $cols));
            $printer->text($this->lr("Date:", $order->created_at->format('d/m/y h:i A'), $cols));
            $printer->text($this->lr("Table:", $order->table->name ?? '-', $cols));
            $printer->text($this->lr("Bill To:", ucfirst($order->order_type ?? 'dine_in'), $cols));
            $printer->text(str_repeat('-', $cols) . "\n");

            $printer->setEmphasis(true);
            $printer->text($this->cols4("Item", "Qty", "Rate", "Total", $cols));
            $printer->setEmphasis(false);

            $subTotal = 0.0; $totalCgst = 0.0; $totalSgst = 0.0;
            $totalQty = 0;   $totalItems = 0;

            foreach ($order->orderItems as $it) {
                $qty   = (int) $it->quantity;
                $rate  = (float) $it->base_price;
                $tax   = optional($it->item->taxSetting);
                $taxRate = (float) ($tax?->rate ?? 0);
                $isInclusive = (bool) ($it->item->is_tax_inclusive);

                if ($taxRate > 0) {
                    if ($isInclusive) {
                        $basePrice   = $rate - ($rate * $taxRate / 100);
                        $taxPerUnit  = ($rate * $taxRate / 100);
                    } else {
                        $basePrice   = $rate;
                        $taxPerUnit  = ($basePrice * $taxRate / 100);
                    }
                } else {
                    $basePrice  = $rate;
                    $taxPerUnit = 0.0;
                }

                $cgst = $taxPerUnit / 2.0;
                $sgst = $taxPerUnit / 2.0;

                $lineBaseTotal = $basePrice * $qty;

                $subTotal  += $lineBaseTotal;
                $totalCgst += $cgst * $qty;
                $totalSgst += $sgst * $qty;
                $totalQty  += $qty;
                $totalItems++;

                $name = $it->item->name . ($it->variant ? " ({$it->variant->name})" : "");

                foreach ($this->wrap($name, 16) as $i => $part) {
                    if ($i === 0) {
                        $printer->text($this->cols4(
                            $part,
                            (string)$qty,
                            $this->money($basePrice),
                            $this->money($lineBaseTotal),
                            $cols
                        ));
                    } else {
                        $printer->text($part . "\n");
                    }
                }
            }

            $taxTotal   = $totalCgst + $totalSgst;
            $grandTotal = $subTotal + $taxTotal;

            $printer->text(str_repeat('-', $cols) . "\n");
            $printer->text("Items: $totalItems   Qty: $totalQty\n");
            $printer->text($this->lr("Sub Total:", $this->money($subTotal), $cols));
            if ($taxTotal > 0) {
                $printer->text($this->lr("CGST:", $this->money($totalCgst), $cols));
                $printer->text($this->lr("SGST:", $this->money($totalSgst), $cols));
                $printer->text($this->lr("Tax Value:", $this->money($taxTotal), $cols));
            }
            $printer->setEmphasis(true);
            $printer->text($this->lr("Grand Total:", $this->money($grandTotal), $cols));
            $printer->setEmphasis(false);

            $printer->text(str_repeat('-', $cols) . "\n");
            $printer->setJustification(Printer::JUSTIFY_CENTER);
            $printer->text("Thank You! Visit Again 🙏\n");

            if ($cutAndPulse) {
                $printer->feed(2);
                $printer->cut();
                // $printer->pulse();
            }
        } finally {
            // ✅ always attempt close if created
            if ($printer instanceof Printer) {
                try { $printer->close(); } catch (Throwable $e) {
                    Log::warning('ESC/POS: close() warning: '.$e->getMessage());
                }
            }
        }
    }

    private function money(float $amt): string
    {
        return number_format($amt, 2);
    }


    private function lr(string $left, string $right, int $width): string
    {
        $l = mb_strimwidth($left, 0, $width - 1, '');
        $r = mb_strimwidth($right, 0, $width - 1, '');
        $spaces = max(1, $width - mb_strlen($l) - mb_strlen($r));
        return $l . str_repeat(' ', $spaces) . $r . "\n";
    }

    private function cols4(string $c1, string $c2, string $c3, string $c4, int $width = 36): string
    {
        $w1 = 16; $w2 = 3; $w3 = 7; $w4 = $width - ($w1 + 1 + $w2 + 1 + $w3 + 1);
        $p1 = str_pad(mb_strimwidth($c1, 0, $w1, '…'), $w1);
        $p2 = str_pad(mb_strimwidth($c2, 0, $w2, ''), $w2, ' ', STR_PAD_LEFT);
        $p3 = str_pad(mb_strimwidth($c3, 0, $w3, ''), $w3, ' ', STR_PAD_LEFT);
        $p4 = str_pad(mb_strimwidth($c4, 0, $w4, ''), $w4, ' ', STR_PAD_LEFT);
        return "$p1 $p2 $p3 $p4\n";
    }

    private function wrap(string $text, int $cols): array
    {
        $out = [];
        $line = '';
        foreach (preg_split('/\s+/', $text) as $w) {
            if (mb_strlen($line . ' ' . $w) <= $cols) {
                $line = trim($line . ' ' . $w);
            } else {
                if ($line !== '') $out[] = $line;
                while (mb_strlen($w) > $cols) {
                    $out[] = mb_substr($w, 0, $cols);
                    $w = mb_substr($w, $cols);
                }
                $line = $w;
            }
        }
        if ($line !== '') $out[] = $line;
        return $out;
    }
}
