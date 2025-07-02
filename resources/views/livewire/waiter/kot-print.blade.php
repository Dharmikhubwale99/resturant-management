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
</style>

<div class="print-area w-[58mm] mx-auto text-[12px] leading-snug font-mono px-2 py-1">
    <div class="text-center mb-1">
        <p class="text-xs">{{ $kot->created_at->format('d/m/y H:i') }}</p>
        <h2 class="font-bold text-sm">KOT – {{ $kot->kot_number }}</h2>
        <p class="text-[11px] font-semibold">Dine In</p>
        <p class="text-[11px] mb-1">Table No: {{ $kot->table->name ?? 'N/A' }}</p>
        <hr class="border-t border-dashed my-1">
    </div>

    <table class="w-full text-left mb-1">
        <thead class="border-b border-dashed text-xs font-bold">
            <tr>
                <th class="w-2/3">Item</th>
                <th class="w-1/3 text-right">Qty.</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($kot->kotItems as $item)
                <tr>
                    <td class="pr-1">
                        {{ $item->item->name ?? 'Item Deleted' }}
                        @if ($item->variant)
                            <strong>({{ $item->variant->name }})</strong>
                        @endif
                    </td>
                    <td class="text-right">{{ $item->quantity }}</td>
                </tr>
                @if ($item->special_notes)
                    <tr>
                        <td colspan="2" class="text-[10px] italic text-gray-600 pl-1">[Note] {{ $item->special_notes }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <script>
        window.onload = () => window.print();
    </script>
</div>
