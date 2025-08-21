<?php

// app/Http/Controllers/WindowsPrintController.php
namespace App\Http\Controllers;

use App\Models\Kot;

class WindowsPrintController extends Controller
{
    // QZ Tray જે data પ્રિન્ટ કરશે (raw text/ESC-POS)
    public function escpos(Kot $kot)
    {
        $kot->load(['kotItems.item','kotItems.variant','table']);
        $W = 32; // 58mm ≈ 32 chars/line
        $line = str_repeat('-', $W)."\n";

        $t  = "KOT #".$kot->kot_number."\n";
        $t .= $kot->created_at->format('d/m/Y H:i')."\n";
        $t .= "Table: ".($kot->table->name ?? 'N/A')."\n".$line;

        foreach ($kot->kotItems as $row) {
            $name = trim(($row->item->name ?? 'Item')
                    .($row->variant ? ' ('.$row->variant->name.')' : ''));
            $qty  = (string) $row->quantity;
            $left = mb_strimwidth($name, 0, $W - (strlen($qty)+3), '');
            $t   .= $left.' x'.$qty."\n";
            if ($row->special_notes) {
                $t .= "[Note] ".$row->special_notes."\n";
            }
        }
        $t .= $line."Dine In\n-- Thank You --\n";

        // (ઓપ્શનલ) કટ કમાન્ડ: ESC/POS  GS V 66 0
        $t .= "\x1D\x56\x42\x00";

        return response($t, 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    // Launch page: QZ Tray JS જોડે direct print
    public function launch(Kot $kot)
    {
        return view('prints.windows-kot-qz', ['kotId' => $kot->id]);
    }
}
