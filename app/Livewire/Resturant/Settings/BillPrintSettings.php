<?php

namespace App\Livewire\Resturant\Settings;

use Livewire\Component;
use App\Models\BillPrintSetting;
use Livewire\Attributes\Layout;

class BillPrintSettings extends Component
{
    public bool $show_logo = false;
    public bool $show_address = true;
    public bool $show_gstin = true;
    public bool $show_order_no = true;
    public bool $show_table = true;
    public bool $show_order_type = true;
    public bool $show_items_header = true;
    public bool $show_item_notes = true;
    public bool $show_tax_breakup = true;
    public bool $show_footer_msg = true;

    public string $footer_msg = '— Thank You! Visit Again —';
    public string $paper = '55mm';
    public int $font_small_format = 4;
    public bool $round_grand_total = false;
    public string $round_mode = 'nearest';
    public ?int $cpl_override = null;

    public array $styles = [];

    // NOTE: no payment/balance here anymore
    public array $styleKeys = [
        'header_restaurant_name' => 'Header: Restaurant Name',
        'header_address'         => 'Header: Address (bold only)',
        'header_gstin'           => 'Header: GSTIN (bold only)',

        'meta_bill_no'           => 'Meta: Bill No',
        'meta_order_no'          => 'Meta: Order No (bold only)',
        'meta_date'              => 'Meta: Date',
        'meta_table_type'        => 'Meta: Table/Type (bold only)',

        'items_header'           => 'Items Header',
        'item_name_line'         => 'Item Line 1',
        'item_name_wrap'         => 'Item Wrap (next lines)',
        'item_notes'             => 'Item Notes',

        'totals_row'             => 'Totals: Items/Qty',
        'sub_total'              => 'Totals: Sub Total',
        'cgst'                   => 'Totals: CGST',
        'sgst'                   => 'Totals: SGST',
        'tax_value'              => 'Totals: Tax Value',
        'round_off'              => 'Totals: Round Off',
        'grand_total'            => 'Totals: Grand Total',

        'footer_msg'             => 'Footer Message',
    ];

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.settings.bill-print-settings');
    }

    public function mount()
    {
        $restaurant = auth()->user()?->restaurant;
        abort_unless($restaurant, 403);

        $merged = optional($restaurant->billPrintSetting)->merged()
                ?? BillPrintSetting::defaults();

        foreach ([
            'show_logo','show_address','show_gstin','show_order_no','show_table','show_order_type',
            'show_items_header','show_item_notes','show_tax_breakup','show_footer_msg','round_grand_total',
        ] as $b) {
            $this->{$b} = (bool) ($merged[$b] ?? false);
        }

        $this->footer_msg        = (string) ($merged['footer_msg'] ?? $this->footer_msg);
        $this->paper             = (string) ($merged['paper'] ?? '55mm');
        $this->font_small_format = (int)    ($merged['font_small_format'] ?? 4);
        $this->round_mode        = (string) ($merged['round_mode'] ?? 'nearest');
        $this->cpl_override      = $merged['cpl_override'] !== null ? (int)$merged['cpl_override'] : null;

        // deep-merge styles
        $def = BillPrintSetting::defaults();
        $this->styles = array_replace($def['styles'], $merged['styles'] ?? []);
    }

    protected function rules(): array
    {
        return [
            'show_logo'         => ['boolean'],
            'show_address'      => ['boolean'],
            'show_gstin'        => ['boolean'],
            'show_order_no'     => ['boolean'],
            'show_table'        => ['boolean'],
            'show_order_type'   => ['boolean'],
            'show_items_header' => ['boolean'],
            'show_item_notes'   => ['boolean'],
            'show_tax_breakup'  => ['boolean'],
            'show_footer_msg'   => ['boolean'],

            'footer_msg'        => ['nullable','string','max:200'],
            'paper'             => ['required','in:55mm,80mm'],
            'font_small_format' => ['required','integer','min:0','max:9'],
            'round_grand_total' => ['boolean'],
            'round_mode'        => ['required','in:nearest,up,down'],
            'cpl_override'      => ['nullable','integer','min:20','max:80'],

            'styles'            => ['array'],
            'styles.*.bold'     => ['nullable','integer','in:0,1'],
            'styles.*.align'    => ['nullable','integer','in:0,1,2'],
            'styles.*.format'   => ['nullable','integer','min:0','max:9'],
        ];
    }

    public function save()
    {
        $this->validate();

        $restaurant = auth()->user()?->restaurant;
        abort_unless($restaurant, 403);

        // sanitize per known keys
        $clean = [];
        foreach ($this->styleKeys as $key => $_label) {
            $row = $this->styles[$key] ?? [];
            $clean[$key] = [
                'bold'   => isset($row['bold'])   ? (int)$row['bold']   : 0,
                'align'  => isset($row['align'])  ? (int)$row['align']  : 0,
                'format' => isset($row['format']) ? (int)$row['format'] : 0,
            ];
        }

        $payload = [
            'show_logo'         => (bool)$this->show_logo,
            'show_address'      => (bool)$this->show_address,
            'show_gstin'        => (bool)$this->show_gstin,
            'show_order_no'     => (bool)$this->show_order_no,
            'show_table'        => (bool)$this->show_table,
            'show_order_type'   => (bool)$this->show_order_type,
            'show_items_header' => (bool)$this->show_items_header,
            'show_item_notes'   => (bool)$this->show_item_notes,
            'show_tax_breakup'  => (bool)$this->show_tax_breakup,
            'show_footer_msg'   => (bool)$this->show_footer_msg,

            'footer_msg'        => (string)$this->footer_msg,
            'paper'             => (string)$this->paper,
            'font_small_format' => (int)$this->font_small_format,
            'round_grand_total' => (bool)$this->round_grand_total,
            'round_mode'        => (string)$this->round_mode,
            'cpl_override'      => $this->cpl_override !== '' ? $this->cpl_override : null,

            'styles'            => $clean,
        ];

        BillPrintSetting::updateOrCreate(
            ['restaurant_id' => $restaurant->id],
            ['options' => $payload]
        );

        $this->dispatch('saved');
        session()->flash('status', 'Bill print settings saved.');
        return redirect()->route('restaurant.dashboard');
    }
}
