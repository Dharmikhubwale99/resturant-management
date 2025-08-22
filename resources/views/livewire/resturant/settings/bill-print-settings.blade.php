{{-- resources/views/livewire/resturant/settings/bill-print-settings.blade.php --}}
<div class="max-w-8xl mx-auto p-4 sm:p-6 lg:p-8">
    <h2 class="text-2xl sm:text-3xl font-semibold mb-4 sm:mb-6">Bill Print Settings</h2>

    @if (session('status'))
        <div class="mb-4 inline-block border rounded-full px-3 py-1 text-sm">{{ session('status') }}</div>
    @endif

    <form wire:submit.prevent="save" class="space-y-5 sm:space-y-6">
        {{-- BASICS --}}
        <fieldset class="border rounded-lg p-3 sm:p-4">
            <legend class="px-2 text-base sm:text-lg">Basics</legend>

            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4 items-start mb-3 sm:mb-4">
                <label class="flex items-center gap-2"><input type="checkbox" wire:model="show_logo"><span>Show
                        Logo</span></label>
                {{-- <label class="flex items-center gap-2"><input type="checkbox" wire:model="show_address"><span>Address</span></label>
          <label class="flex items-center gap-2"><input type="checkbox" wire:model="show_gstin"><span>GSTIN</span></label>
          <label class="flex items-center gap-2"><input type="checkbox" wire:model="show_order_no"><span>Order No</span></label>
          <label class="flex items-center gap-2"><input type="checkbox" wire:model="show_table"><span>Table</span></label>
          <label class="flex items-center gap-2"><input type="checkbox" wire:model="show_order_type"><span>Order Type</span></label> --}}
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <label class="flex flex-col">
                    <span class="text-sm">Paper</span>
                    <select wire:model="paper" class="border rounded px-3 py-2 w-full sm:w-36">
                        <option value="55mm">55mm</option>
                        <option value="80mm">80mm</option>
                    </select>
                    @error('paper')
                        <small class="text-red-600">{{ $message }}</small>
                    @enderror
                </label>

                {{-- <label class="flex flex-col">
            <span class="text-sm">Chars/Line Override</span>
            <input type="number" wire:model="cpl_override" min="20" max="80" class="border rounded px-3 py-2 w-full sm:w-36" />
            @error('cpl_override') <small class="text-red-600">{{ $message }}</small> @enderror
          </label> --}}

                <label class="flex items-center gap-2">
                    <input type="checkbox" wire:model="round_grand_total"><span>Round Grand Total</span>
                </label>

                <label class="flex flex-col">
                    <span class="text-sm">Round Mode</span>
                    <select wire:model="round_mode" class="border rounded px-3 py-2 w-full sm:w-36">
                        <option value="nearest">Nearest</option>
                        <option value="up">Up</option>
                        <option value="down">Down</option>
                    </select>
                    @error('round_mode')
                        <small class="text-red-600">{{ $message }}</small>
                    @enderror
                </label>

                <label class="flex flex-col sm:col-span-2 lg:col-span-1">
                    <span class="text-sm">Small Font "format"</span>
                    <input type="number" wire:model="font_small_format" min="0" max="9"
                        class="border rounded px-3 py-2 w-full sm:w-36" />
                    @error('font_small_format')
                        <small class="text-red-600">{{ $message }}</small>
                    @enderror
                </label>
            </div>
        </fieldset>

        {{-- HEADER & META (BOLD ONLY) --}}
        <fieldset class="border rounded-lg p-3 sm:p-4">
            <legend class="px-2 text-base sm:text-lg">Header &amp; Meta (Bold only)</legend>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Address --}}
                <div class="border rounded p-3 sm:p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-base">Address</h4>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox"
                                wire:model="show_address"><span>Show</span></label>
                    </div>
                    <label class="flex flex-col">
                        <span class="text-sm">Bold</span>
                        <select wire:model="styles.header_address.bold" class="border rounded px-3 py-2 w-full">
                            <option value="0">0</option>
                            <option value="1">1</option>
                        </select>
                    </label>
                </div>

                {{-- GSTIN --}}
                <div class="border rounded p-3 sm:p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-base">GSTIN</h4>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox"
                                wire:model="show_gstin"><span>Show</span></label>
                    </div>
                    <label class="flex flex-col">
                        <span class="text-sm">Bold</span>
                        <select wire:model="styles.header_gstin.bold" class="border rounded px-3 py-2 w-full">
                            <option value="0">0</option>
                            <option value="1">1</option>
                        </select>
                    </label>
                </div>

                {{-- Order No --}}
                <div class="border rounded p-3 sm:p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-base">Order No</h4>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox"
                                wire:model="show_order_no"><span>Show</span></label>
                    </div>
                    <label class="flex flex-col">
                        <span class="text-sm">Bold</span>
                        <select wire:model="styles.meta_order_no.bold" class="border rounded px-3 py-2 w-full">
                            <option value="0">0</option>
                            <option value="1">1</option>
                        </select>
                    </label>
                </div>

                {{-- Table / Order Type (combined) --}}
                <div class="border rounded p-3 sm:p-4 lg:col-span-2">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-base">Table / Order Type</h4>
                        <div class="flex gap-4 text-sm">
                            <label class="flex items-center gap-2"><input type="checkbox"
                                    wire:model="show_table"><span>Table</span></label>
                            <label class="flex items-center gap-2"><input type="checkbox"
                                    wire:model="show_order_type"><span>Order Type</span></label>
                        </div>
                    </div>
                    <label class="flex flex-col max-w-xs">
                        <span class="text-sm">Bold</span>
                        <select wire:model="styles.meta_table_type.bold" class="border rounded px-3 py-2 w-full">
                            <option value="0">0</option>
                            <option value="1">1</option>
                        </select>
                    </label>
                </div>
            </div>
        </fieldset>

        {{-- SECTION CARDS (items + totals) --}}
        <fieldset class="border rounded-lg p-3 sm:p-4">
            <legend class="px-2 text-base sm:text-lg">Sections (toggle + style)</legend>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Items Header --}}
                <div class="border rounded p-3 sm:p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-base">Items Header</h4>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox"
                                wire:model="show_items_header"><span>Show</span></label>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="flex flex-col">
                            <span class="text-sm">Bold</span>
                            <select wire:model="styles.items_header.bold" class="border rounded px-3 py-2 w-full">
                                <option value="0">0</option>
                                <option value="1">1</option>
                            </select>
                        </label>
                        <label class="flex flex-col">
                            <span class="text-sm">Align</span>
                            <select wire:model="styles.items_header.align" class="border rounded px-3 py-2 w-full">
                                <option value="0">Left</option>
                                <option value="1">Center</option>
                                <option value="2">Right</option>
                            </select>
                        </label>
                        <label class="flex flex-col">
                            <span class="text-sm">Format</span>
                            <input type="number" min="0" max="9"
                                wire:model="styles.items_header.format" class="border rounded px-3 py-2 w-full" />
                        </label>
                    </div>
                </div>

                {{-- Item Line --}}
                <div class="border rounded p-3 sm:p-4">
                    <h4 class="font-semibold text-base mb-2">Item Line</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="flex flex-col">
                            <span class="text-sm">Bold</span>
                            <select wire:model="styles.item_name_line.bold" class="border rounded px-3 py-2 w-full">
                                <option value="0">0</option>
                                <option value="1">1</option>
                            </select>
                        </label>
                        <label class="flex flex-col">
                            <span class="text-sm">Align</span>
                            <select wire:model="styles.item_name_line.align" class="border rounded px-3 py-2 w-full">
                                <option value="0">Left</option>
                                <option value="1">Center</option>
                                <option value="2">Right</option>
                            </select>
                        </label>
                        <label class="flex flex-col">
                            <span class="text-sm">Format</span>
                            <input type="number" min="0" max="9"
                                wire:model="styles.item_name_line.format" class="border rounded px-3 py-2 w-full" />
                        </label>
                    </div>
                </div>

                {{-- Item Wrap --}}
                <div class="border rounded p-3 sm:p-4">
                    <h4 class="font-semibold text-base mb-2">Item Wrap (next lines)</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="flex flex-col">
                            <span class="text-sm">Bold</span>
                            <select wire:model="styles.item_name_wrap.bold" class="border rounded px-3 py-2 w-full">
                                <option value="0">0</option>
                                <option value="1">1</option>
                            </select>
                        </label>
                        <label class="flex flex-col">
                            <span class="text-sm">Align</span>
                            <select wire:model="styles.item_name_wrap.align" class="border rounded px-3 py-2 w-full">
                                <option value="0">Left</option>
                                <option value="1">Center</option>
                                <option value="2">Right</option>
                            </select>
                        </label>
                        <label class="flex flex-col">
                            <span class="text-sm">Format</span>
                            <input type="number" min="0" max="9"
                                wire:model="styles.item_name_wrap.format" class="border rounded px-3 py-2 w-full" />
                        </label>
                    </div>
                </div>

                {{-- Item Notes --}}
                <div class="border rounded p-3 sm:p-4">
                    <div class="flex items-center justify-between mb-2">
                        <h4 class="font-semibold text-base">Item Notes</h4>
                        <label class="flex items-center gap-2 text-sm"><input type="checkbox"
                                wire:model="show_item_notes"><span>Show</span></label>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="flex flex-col">
                            <span class="text-sm">Bold</span>
                            <select wire:model="styles.item_notes.bold" class="border rounded px-3 py-2 w-full">
                                <option value="0">0</option>
                                <option value="1">1</option>
                            </select>
                        </label>
                        <label class="flex flex-col">
                            <span class="text-sm">Align</span>
                            <select wire:model="styles.item_notes.align" class="border rounded px-3 py-2 w-full">
                                <option value="0">Left</option>
                                <option value="1">Center</option>
                                <option value="2">Right</option>
                            </select>
                        </label>
                        <label class="flex flex-col">
                            <span class="text-sm">Format</span>
                            <input type="number" min="0" max="9"
                                wire:model="styles.item_notes.format" class="border rounded px-3 py-2 w-full" />
                        </label>
                    </div>
                </div>

                {{-- Totals Row --}}
                <div class="border rounded p-3 sm:p-4">
                    <h4 class="font-semibold text-base mb-2">Totals Row (Items/Qty)</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="flex flex-col">
                            <span class="text-sm">Bold</span>
                            <select wire:model="styles.totals_row.bold" class="border rounded px-3 py-2 w-full">
                                <option value="0">0</option>
                                <option value="1">1</option>
                            </select>
                        </label>
                        <label class="flex flex-col">
                            <span class="text-sm">Align</span>
                            <select wire:model="styles.totals_row.align" class="border rounded px-3 py-2 w-full">
                                <option value="0">Left</option>
                                <option value="1">Center</option>
                                <option value="2">Right</option>
                            </select>
                        </label>
                        <label class="flex flex-col">
                            <span class="text-sm">Format</span>
                            <input type="number" min="0" max="9"
                                wire:model="styles.totals_row.format" class="border rounded px-3 py-2 w-full" />
                        </label>
                    </div>
                </div>

                {{-- Sub Total --}}
                <div class="border rounded p-3 sm:p-4">
                    <h4 class="font-semibold text-base mb-2">Sub Total</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="flex flex-col">
                            <span class="text-sm">Bold</span>
                            <select wire:model="styles.sub_total.bold" class="border rounded px-3 py-2 w-full">
                                <option value="0">0</option>
                                <option value="1">1</option>
                            </select>
                        </label>
                        <label class="flex flex-col">
                            <span class="text-sm">Align</span>
                            <select wire:model="styles.sub_total.align" class="border rounded px-3 py-2 w-full">
                                <option value="0">Left</option>
                                <option value="1">Center</option>
                                <option value="2">Right</option>
                            </select>
                        </label>
                        <label class="flex flex-col">
                            <span class="text-sm">Format</span>
                            <input type="number" min="0" max="9" wire:model="styles.sub_total.format"
                                class="border rounded px-3 py-2 w-full" />
                        </label>
                    </div>
                </div>

                {{-- Grand Total --}}
                <div class="border rounded p-3 sm:p-4">
                    <h4 class="font-semibold text-base mb-2">Grand Total</h4>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="flex flex-col">
                            <span class="text-sm">Bold</span>
                            <select wire:model="styles.grand_total.bold" class="border rounded px-3 py-2 w-full">
                                <option value="0">0</option>
                                <option value="1">1</option>
                            </select>
                        </label>
                        <label class="flex flex-col">
                            <span class="text-sm">Align</span>
                            <select wire:model="styles.grand_total.align" class="border rounded px-3 py-2 w-full">
                                <option value="0">Left</option>
                                <option value="1">Center</option>
                                <option value="2">Right</option>
                            </select>
                        </label>
                        <label class="flex flex-col">
                            <span class="text-sm">Format</span>
                            <input type="number" min="0" max="9"
                                wire:model="styles.grand_total.format" class="border rounded px-3 py-2 w-full" />
                        </label>
                    </div>
                </div>
            </div>

            {{-- Tax Breakup full width --}}
            <div class="border rounded p-3 sm:p-4 mt-4">
                <div class="flex items-center justify-between mb-2">
                    <h4 class="font-semibold text-base">Tax Breakup</h4>
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox"
                            wire:model="show_tax_breakup"><span>Show</span></label>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="border rounded p-2">
                        <div class="text-xs text-gray-600 mb-2">CGST</div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <label class="flex flex-col">
                                <span class="text-xs">Bold</span>
                                <select wire:model="styles.cgst.bold" class="border rounded px-3 py-2 w-full">
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                </select>
                            </label>
                            <label class="flex flex-col">
                                <span class="text-xs">Align</span>
                                <select wire:model="styles.cgst.align" class="border rounded px-3 py-2 w-full">
                                    <option value="0">Left</option>
                                    <option value="1">Center</option>
                                    <option value="2">Right</option>
                                </select>
                            </label>
                            <label class="flex flex-col">
                                <span class="text-xs">Format</span>
                                <input type="number" min="0" max="9" wire:model="styles.cgst.format"
                                    class="border rounded px-3 py-2 w-full" />
                            </label>
                        </div>
                    </div>

                    <div class="border rounded p-2">
                        <div class="text-xs text-gray-600 mb-2">SGST</div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <label class="flex flex-col">
                                <span class="text-xs">Bold</span>
                                <select wire:model="styles.sgst.bold" class="border rounded px-3 py-2 w-full">
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                </select>
                            </label>
                            <label class="flex flex-col">
                                <span class="text-xs">Align</span>
                                <select wire:model="styles.sgst.align" class="border rounded px-3 py-2 w-full">
                                    <option value="0">Left</option>
                                    <option value="1">Center</option>
                                    <option value="2">Right</option>
                                </select>
                            </label>
                            <label class="flex flex-col">
                                <span class="text-xs">Format</span>
                                <input type="number" min="0" max="9" wire:model="styles.sgst.format"
                                    class="border rounded px-3 py-2 w-full" />
                            </label>
                        </div>
                    </div>

                    <div class="border rounded p-2">
                        <div class="text-xs text-gray-600 mb-2">Tax Value</div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <label class="flex flex-col">
                                <span class="text-xs">Bold</span>
                                <select wire:model="styles.tax_value.bold" class="border rounded px-3 py-2 w-full">
                                    <option value="0">0</option>
                                    <option value="1">1</option>
                                </select>
                            </label>
                            <label class="flex flex-col">
                                <span class="text-xs">Align</span>
                                <select wire:model="styles.tax_value.align" class="border rounded px-3 py-2 w-full">
                                    <option value="0">Left</option>
                                    <option value="1">Center</option>
                                    <option value="2">Right</option>
                                </select>
                            </label>
                            <label class="flex flex-col">
                                <span class="text-xs">Format</span>
                                <input type="number" min="0" max="9"
                                    wire:model="styles.tax_value.format" class="border rounded px-3 py-2 w-full" />
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            <x-form.button type="submit" title="Save Settings" class="bg-black hover:bg-black/80 text-white"
                wireTarget="save" />
        </div>
    </form>
</div>
