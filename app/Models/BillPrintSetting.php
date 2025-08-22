<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillPrintSetting extends Model
{
    protected $fillable = ['restaurant_id', 'options'];
    protected $casts = ['options' => 'array'];

    public static function defaults(): array
    {
        return [
            // Toggles / basics
            'show_logo'          => false,
            'show_address'       => true,
            'show_gstin'         => true,
            'show_order_no'      => true,
            'show_table'         => true,
            'show_order_type'    => true,
            'show_items_header'  => true,
            'show_item_notes'    => true,
            'show_tax_breakup'   => true,
            'show_footer_msg'    => true,

            'footer_msg'         => '— Thank You! Visit Again —',
            'paper'              => '55mm',       // '55mm' | '80mm'
            'font_small_format'  => 4,
            'round_grand_total'  => false,
            'round_mode'         => 'nearest',    // 'up' | 'down' | 'nearest'
            'cpl_override'       => null,         // chars per line

            // Per-section styles
            'styles' => [
                'header_restaurant_name' => ['bold'=>1,'align'=>1,'format'=>0],
                // these five sections will only expose "bold" in UI (align/format still stored but ignored)
                'header_address'         => ['bold'=>0,'align'=>1,'format'=>4],
                'header_gstin'           => ['bold'=>0,'align'=>1,'format'=>4],
                'meta_bill_no'           => ['bold'=>1,'align'=>0,'format'=>0],
                'meta_order_no'          => ['bold'=>0,'align'=>0,'format'=>0],
                'meta_date'              => ['bold'=>0,'align'=>0,'format'=>0],
                'meta_table_type'        => ['bold'=>1,'align'=>0,'format'=>0],

                'items_header'           => ['bold'=>1,'align'=>0,'format'=>4],
                'item_name_line'         => ['bold'=>0,'align'=>0,'format'=>4],
                'item_name_wrap'         => ['bold'=>0,'align'=>0,'format'=>4],
                'item_notes'             => ['bold'=>0,'align'=>0,'format'=>4],

                'totals_row'             => ['bold'=>0,'align'=>0,'format'=>4],
                'sub_total'              => ['bold'=>0,'align'=>2,'format'=>0],
                'cgst'                   => ['bold'=>0,'align'=>2,'format'=>0],
                'sgst'                   => ['bold'=>0,'align'=>2,'format'=>0],
                'tax_value'              => ['bold'=>0,'align'=>2,'format'=>0],
                'round_off'              => ['bold'=>0,'align'=>2,'format'=>0],
                'grand_total'            => ['bold'=>1,'align'=>2,'format'=>0],

                'footer_msg'             => ['bold'=>1,'align'=>1,'format'=>0],
            ],
        ];
    }

    public function merged(): array
    {
        $db = $this->options ?? [];
        $def = self::defaults();
        $db['styles'] = array_replace($def['styles'], $db['styles'] ?? []);
        return array_replace($def, $db);
    }
}
