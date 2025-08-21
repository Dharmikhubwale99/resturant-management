<?php

// app/Http/Controllers/BluetoothPrintController.php
namespace App\Http\Controllers;

use App\Models\Kot;
use Illuminate\Http\Request;

class BluetoothPrintController extends Controller
{
    /**
     * RESPONSE URL → JSON that the app will print.
     * Important: no extra output/whitespace. Keep it clean JSON.
     */
    public function kotJson(Request $request, Kot $kot)
    {
        $kot->load(['kotItems.item', 'kotItems.variant', 'table']);

        $arr = [];

        // Title
        $arr[] = (object)[
            'type'    => 0,                 // text
            'content' => 'KOT #'.$kot->kot_number,
            'bold'    => 1,
            'align'   => 1,                 // center
            'format'  => 0,                 // normal
        ];
        $arr[] = (object)[
            'type'    => 0,
            'content' => $kot->created_at->format('d/m/Y H:i'),
            'bold'    => 0,
            'align'   => 1,
            'format'  => 0,
        ];
        $arr[] = (object)[
            'type'    => 0,
            'content' => 'Table: '.($kot->table->name ?? 'N/A'),
            'bold'    => 0,
            'align'   => 1,
            'format'  => 0,
        ];

        // Separator
        $arr[] = (object)[
            'type'    => 4, // HTML
            'content' => '<div style="border-top:1px dashed #000;margin:6px 0"></div>',
        ];

        // Header row (optional)
        $arr[] = (object)[
            'type'    => 0,
            'content' => 'Item                          Qty',
            'bold'    => 1,
            'align'   => 0,
            'format'  => 4, // small
        ];

        // Items
        foreach ($kot->kotItems as $row) {
            $name = trim(($row->item->name ?? 'Item')
                     . ($row->variant ? ' ('.$row->variant->name.')' : ''));

            // line with item + qty (simple spacing)
            $arr[] = (object)[
                'type'    => 0,
                'content' => $name.'  x'.$row->quantity,
                'bold'    => 0,
                'align'   => 0,
                'format'  => 0,
            ];

            // special notes (if any)
            if ($row->special_notes) {
                $arr[] = (object)[
                    'type'    => 4, // HTML for small italic
                    'content' => '<div style="font-size:12px;font-style:italic;margin-left:6px">[Note] '
                                 . e($row->special_notes) . '</div>',
                ];
            }
        }

        // Footer
        $arr[] = (object)[
            'type'    => 4,
            'content' => '<div style="border-top:1px dashed #000;margin:6px 0"></div>',
        ];
        $arr[] = (object)[
            'type'    => 0,
            'content' => 'Dine In',
            'bold'    => 0,
            'align'   => 1,
            'format'  => 0,
        ];
        $arr[] = (object)[
            'type'    => 0,
            'content' => '— Thank You —',
            'bold'    => 1,
            'align'   => 1,
            'format'  => 0,
        ];

        // Important: JSON_FORCE_OBJECT to match app's sample; UNESCAPED_UNICODE for multilingual
        return response()->json($arr, 200, [], JSON_FORCE_OBJECT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * LAUNCH PAGE → opens my.bluetoothprint.scheme://<RESPONSEURL>
     * This is what you call from UI (Livewire event).
     */
    public function launchKot(Kot $kot)
    {
        // absolute URL that phone can reach (domain or LAN IP)
        $responseUrl = route('bt.kot.response', ['kot' => $kot->id], true);

        return response()->view('prints.launch-bt', [
            'responseUrl' => $responseUrl,
        ]);
    }
}
