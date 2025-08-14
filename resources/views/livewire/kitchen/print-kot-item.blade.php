<style>
    @media print {
        @page {
            size: 58mm auto;
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

    /* small utility classes used below */
    body {
        font-family: monospace;
    }

    .w-58 {
        width: 58mm;
    }

    .text-right {
        text-align: right;
    }

    .text-center {
        text-align: center;
    }

    .bold {
        font-weight: bold;
    }

    .xs {
        font-size: 11px;
    }

    .sm {
        font-size: 12px;
    }

    .mb-1 {
        margin-bottom: 4px;
    }

    .mb-2 {
        margin-bottom: 6px;
    }

    .px-2 {
        padding-left: 2mm;
        padding-right: 2mm;
    }

    .py-2 {
        padding-top: 2mm;
        padding-bottom: 2mm;
    }

    .hr {
        border-top: 1px dashed #000;
        margin: 4px 0;
        height: 1px;
    }

    .muted {
        color: #444;
        font-style: italic;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    td {
        vertical-align: top;
    }
</style>

<div class="print-area w-58 px-2 py-2 sm">
    <div class="text-center mb-2">
        <div class="xs">{{ $kot->created_at?->format('d/m/y H:i') }}</div>
        <div class="bold">KOT – {{ $kot->kot_number }}</div>
        <div class="xs bold">{{ $kot->order->type ?? 'Dine In' }}</div>
        <div class="xs mb-1">
            Table: {{ $kot->table->name ?? 'N/A' }}
            @if (optional($kot->table->area)->name)
                ({{ $kot->table->area->name }})
            @endif
        </div>
        <div class="hr"></div>
        <div class="bold">ITEM SLIP</div>
        <div class="hr"></div>
    </div>

    <table>
        <thead>
            <tr class="bold xs">
                <td>Item</td>
                <td class="text-right">Qty.</td>
            </tr>
        </thead>
        <tbody>
                <tr>
                    <td>
                        {{ $item->item->name ?? 'Item Deleted' }}
                        @if ($item->variant)
                            <span class="bold">
                                ({{ is_object($item->variant) ? $item->variant->name ?? $item->variant : $item->variant }})
                            </span>
                        @endif
                    </td>
                    <td class="text-right bold">{{ $item->quantity }}</td>
                </tr>
                @if ($item->special_notes)
                    <tr>
                        <td colspan="2" class="xs muted">[Note]  {{ $item->special_notes }}</td>
                    </tr>
                @endif
        </tbody>
    </table>

    <div class="hr"></div>
    <div class="xs">KOT ID: {{ $kot->id }}</div>
</div>

<script>
    window.onload = () => window.print();
    window.onafterprint = () => {
        try {
            window.close();
        } catch (_) {}
    };
</script>
