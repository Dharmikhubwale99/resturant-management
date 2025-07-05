<?php

namespace App\Livewire\Waiter;

use App\Models\Table;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\DB;
use App\Models\{Order, OrderItem, KOT, KOTItem};
use Illuminate\Support\Facades\Auth;
use App\Enums\OrderType;

class Item extends Component
{
    public $items;

    public $categories;

    public $selectedCategory = null;

    public $table_id;

    public $search = '';

    public $cart = [];

    public $showVariantModal = false;

    public $currentItem = null;

    public $variantOptions = [];

    public $selectedVariantId = null;

    public $showNoteModal = false;

    public $noteInput = '';

    public $currentNoteKey = null;

    public $orderTypes = [];

    public $order_type;

    public $kotId;

    public $kotTime;

    public $showTableList = false;
    public $occupiedTables = [];
    public $selectedTable = null;
    public $ordersForTable = [];

    public array $originalKotItemKeys = [];

    public bool $editMode = false;

    #[Layout('components.layouts.waiter.app')]
    public function render()
    {
        return view('livewire.waiter.item', [
            'filteredItems' => $this->getFilteredItems(),
            'cartItems' => $this->cart,
            'cartTotal' => $this->getCartTotal(),
        ]);
    }

    public function mount($table_id)
    {
        $table = Table::findOrFail($table_id);

        $this->items = $table->restaurant->items()->with('variants')->get();

        $this->categories = $this->items->pluck('category')->unique('id')->values();

        $this->orderTypes = collect(OrderType::cases())->mapWithKeys(fn($c) => [$c->value => $c->label()])->toArray();

        $this->editMode = request()->query('mode') === 'edit';
        if (request()->query('mode') === 'edit') {
            $latestKot = KOT::where('table_id', $table_id)->where('status', 'pending')->latest()->first();
            $this->kotId = $latestKot?->kot_number;
            $this->kotTime = $latestKot?->created_at;
            if ($latestKot) {
                $latestKot->items()->each(function ($kotItem) {
                    $key = $kotItem->variant_id ? 'v' . $kotItem->variant_id : $kotItem->item_id;

                    $this->originalKotItemKeys[] = $key;

                    $name = $kotItem->variant_id ? $kotItem->item->name . ' (' . $kotItem->variant->name . ')' : $kotItem->item->name;

                    $this->cart[$key] = [
                        'id' => $key,
                        'item_id' => $kotItem->item_id,
                        'name' => $name,
                        'price' => $kotItem->variant_id ? $kotItem->item->price + $kotItem->variant->price : $kotItem->item->price,
                        'qty' => $kotItem->quantity,
                        'note' => $kotItem->special_notes ?? '',
                    ];
                });
            }
        }
    }

    public function getFilteredItems()
    {
        $collection = $this->selectedCategory ? $this->items->where('category_id', $this->selectedCategory) : $this->items;

        if ($this->search !== '') {
            $collection = $collection->filter(
                fn($i) => str($i->name)
                    ->lower()
                    ->contains(str($this->search)->lower()),
            );
        }

        return $collection;
    }

    public function selectCategory($categoryId)
    {
        $this->selectedCategory = $categoryId;
    }

    public function clearCategory()
    {
        $this->selectedCategory = null;
    }

    private function getCartTotal()
    {
        return collect($this->cart)->sum(fn($item) => $item['qty'] * $item['price']);
    }

    public function itemClicked($itemId)
    {
        $item = $this->items->find($itemId);
        if (!$item) {
            return;
        }

        $this->currentItem = [
            'id' => $item->id,
            'name' => $item->name,
            'price' => $item->price,
        ];

        if ($item->variants->isNotEmpty()) {
            $this->variantOptions = $item->variants
                ->map(
                    fn($v) => [
                        'id' => $v->id,
                        'item_id' => $item->id,
                        'combined_name' => $item->name . ' (' . $v->name . ')',
                        'combined_price' => $item->price + $v->price,
                        'variant_name' => $v->name,
                    ],
                )
                ->toArray();

            $this->selectedVariantId = null;
            $this->showVariantModal = true;
        } else {
            $this->addToCart($item->id, $item->name, $item->price);
        }
    }

    public function addSelectedVariant()
    {
        if ($this->selectedVariantId) {
            $v = collect($this->variantOptions)->firstWhere('id', $this->selectedVariantId);
            if (!$v) {
                return;
            }

            $key = 'v' . $v['id'];
            $this->addToCart($key, $v['combined_name'], $v['combined_price']);
            $this->cart[$key]['item_id'] = $v['item_id'];
        } elseif ($this->currentItem) {
            $this->addToCart($this->currentItem['id'], $this->currentItem['name'], $this->currentItem['price']);
        }

        $this->reset(['showVariantModal', 'variantOptions', 'selectedVariantId', 'currentItem']);
    }

    private function addToCart($key, $name, $price)
    {
        $isEdit = $this->editMode;

        if ($isEdit && in_array($key, $this->originalKotItemKeys)) {

            $existingNewKey = collect(array_keys($this->cart))
                ->first(fn ($k) => str_starts_with($k, $key . '-new'));

            if ($existingNewKey) {
                $this->cart[$existingNewKey]['qty']++;
                return;
            }

            $newKey = $key . '-new';
            $this->cart[$newKey] = [
                'id'      => $newKey,
                'item_id' => str_starts_with($key, 'v') ? null : $key,
                'name'    => $name,
                'price'   => $price,
                'qty'     => 1,
                'note'    => '',
            ];
            return;
        }

        if ($isEdit) {
            $existingKey = collect(array_keys($this->cart))
                ->first(fn ($k) => $k === $key || str_starts_with($k, $key . '-new'));

            if ($existingKey) {
                $this->cart[$existingKey]['qty']++;
            } else {
                $this->cart[$key] = [
                    'id'    => $key,
                    'name'  => $name,
                    'price' => $price,
                    'qty'   => 1,
                    'note'  => '',
                ];
            }
            return;
        }

        if (isset($this->cart[$key])) {
            $this->cart[$key]['qty']++;
        } else {
            $this->cart[$key] = [
                'id'    => $key,
                'name'  => $name,
                'price' => $price,
                'qty'   => 1,
                'note'  => '',
            ];
        }
    }


    public function increment($key)
    {
        $this->cart[$key]['qty']++;
    }

    public function decrement($key)
    {
        if (!isset($this->cart[$key])) {
            return;
        }

        $this->cart[$key]['qty']--;

        if ($this->cart[$key]['qty'] < 1) {
            if ($this->editMode && in_array($key, $this->originalKotItemKeys)) {
                $this->cart[$key]['qty'] = 0;
            } else {
                unset($this->cart[$key]);
            }
        }
    }

    public function remove($key)
    {
        if (!isset($this->cart[$key])) {
            return;
        }

        if ($this->editMode && in_array($key, $this->originalKotItemKeys)) {
            $this->cart[$key]['qty'] = 0;
        } else {
            unset($this->cart[$key]);
        }
    }

    public function updateQty($key, $qty)
    {
        $qty = (int) $qty;

        if (!isset($this->cart[$key])) {
            return;
        }

        if ($qty < 1) {
            if ($this->editMode && in_array($key, $this->originalKotItemKeys)) {
                $this->cart[$key]['qty'] = 0;
            } else {
                unset($this->cart[$key]);
            }
            return;
        }

        $this->cart[$key]['qty'] = $qty;
    }

    public function openNoteModal($key)
    {
        $this->currentNoteKey = $key;
        $this->noteInput = $this->cart[$key]['note'] ?? '';
        $this->showNoteModal = true;
    }

    public function saveNote()
    {
        if ($this->currentNoteKey && isset($this->cart[$this->currentNoteKey])) {
            $this->cart[$this->currentNoteKey]['note'] = $this->noteInput;
        }

        $this->showNoteModal = false;
        $this->noteInput = '';
        $this->currentNoteKey = null;
    }

    public function selectOrderType(string $type): void
    {
        $this->order_type = $type;   // just set; Livewire re-renders buttons
    }


    public function placeOrder()
    {
        if (empty($this->cart)) {
            $this->addError('cart', 'Cart is empty!');
            return;
        }

        $this->validate([
            'order_type' => 'required|in:' . implode(',', array_keys($this->orderTypes))
        ]);

        DB::transaction(function () {
            $restaurantId = auth()->user()?->restaurant_id;

            $subTotal = $this->getCartTotal();
            $discountAmount = 0;
            $taxAmount = 0;
            $totalAmount = $subTotal + $taxAmount - $discountAmount;

            $order = Order::create([
                'restaurant_id' => $restaurantId,
                'table_id' => $this->table_id,
                'user_id' => Auth::id(),
                'order_type' => $this->order_type,
                'status' => 'pending',
                'sub_total' => $subTotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
            ]);

            $kot = Kot::create([
                'table_id' => $this->table_id,
                'order_id' => $order->id,
                'status' => 'pending',
                'printed_at' => null,
            ]);

            $table = Table::findOrFail($this->table_id);
            $table->update([
                'status' => 'occupied',
            ]);

            foreach ($this->cart as $row) {
                $variantId = str_starts_with($row['id'], 'v') ? (int) substr($row['id'], 1) : null;

                $baseItemId = $variantId ? $row['item_id'] : $row['id'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $baseItemId,
                    'variant_id' => $variantId,
                    'quantity' => $row['qty'],
                    'base_price' => $row['price'],
                    'discount_amount' => 0,
                    'total_price' => $row['qty'] * $row['price'],
                    'special_notes' => $row['note'] ?? null,
                    'status' => 'pending',
                ]);

                KOTItem::create([
                    'kot_id' => $kot->id,
                    'item_id' => $baseItemId,
                    'variant_id' => $variantId,
                    'quantity' => $row['qty'],
                    'status' => 'pending',
                    'special_notes' => $row['note'] ?? null,
                ]);
            }
        });

        $this->cart = [];
        $this->orderTypes = [];
        $this->showVariantModal = false;
        return redirect()->route('waiter.dashboard')->with('success', 'Order placed!');
    }

    public function placeOrderAndPrint()
    {
        if (empty($this->cart)) {
            $this->addError('cart', 'Cart is empty!');
            return;
        }

        $this->validate([
            'order_type' => 'required|in:' . implode(',', array_keys($this->orderTypes)),
        ]);

        DB::transaction(function () {
            $restaurantId = auth()->user()?->restaurant_id;

            $subTotal = $this->getCartTotal();
            $discountAmount = 0;
            $taxAmount = 0;
            $totalAmount = $subTotal + $taxAmount - $discountAmount;

            $order = Order::create([
                'restaurant_id' => $restaurantId,
                'table_id' => $this->table_id,
                'user_id' => Auth::id(),
                'order_type' => $this->order_type,
                'status' => 'pending',
                'sub_total' => $subTotal,
                'discount_amount' => $discountAmount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
            ]);

            $kot = Kot::create([
                'table_id' => $this->table_id,
                'order_id' => $order->id,
                'status' => 'pending',
                'printed_at' => now(),
            ]);

            $table = Table::findOrFail($this->table_id);
            $table->update([
                'status' => 'occupied',
            ]);

            foreach ($this->cart as $row) {
                $variantId = str_starts_with($row['id'], 'v') ? (int) substr($row['id'], 1) : null;

                $baseItemId = $variantId ? $row['item_id'] : $row['id'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $baseItemId,
                    'variant_id' => $variantId,
                    'quantity' => $row['qty'],
                    'base_price' => $row['price'],
                    'discount_amount' => 0,
                    'total_price' => $row['qty'] * $row['price'],
                    'special_notes' => $row['note'] ?? null,
                    'status' => 'pending',
                ]);

                KOTItem::create([
                    'kot_id' => $kot->id,
                    'item_id' => $baseItemId,
                    'variant_id' => $variantId,
                    'quantity' => $row['qty'],
                    'status' => 'pending',
                    'special_notes' => $row['note'] ?? null,
                ]);
            }
            $this->dispatch('printKot', kotId: $kot->id);
        });

        $this->reset(['cart', 'showVariantModal', 'noteInput', 'currentNoteKey']);
        return redirect()->route('waiter.dashboard')->with('success', 'Order placed!');
    }

    public function updateOrder()
    {
        if (empty($this->cart)) {
            $this->addError('cart', 'Cart is empty!');
            return;
        }

        $this->validate([
            'order_type' => 'required|in:' . implode(',', array_keys($this->orderTypes)),
        ]);

        DB::transaction(function () {
            $order = Order::where('table_id', $this->table_id)->where('status', 'pending')->latest()->firstOrFail();

            $kot = KOT::create([
                'table_id' => $this->table_id,
                'order_id' => $order->id,
                'status' => 'pending',
                'printed_at' => null,
            ]);

            $addToSubTotal = 0;

            foreach ($this->cart as $key => $row) {
                if (in_array($key, $this->originalKotItemKeys)) {
                    continue;
                }

                $variantId = str_starts_with($key, 'v') ? (int) substr($key, 1) : null;
                $baseItemId = $variantId
                    ? $row['item_id']
                    : (int) preg_replace('/-new\d*/', '', $row['id']);
                $lineTotal = $row['qty'] * $row['price'];

                OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $baseItemId,
                    'variant_id' => $variantId,
                    'quantity' => $row['qty'],
                    'base_price' => $row['price'],
                    'discount_amount' => 0,
                    'total_price' => $lineTotal,
                    'special_notes' => $row['note'] ?? null,
                    'status' => 'pending',
                ]);

                KOTItem::create([
                    'kot_id' => $kot->id,
                    'order_id' => $order->id,
                    'item_id' => $baseItemId,
                    'variant_id' => $variantId,
                    'quantity' => $row['qty'],
                    'status' => 'pending',
                    'special_notes' => $row['note'] ?? null,
                ]);

                $addToSubTotal += $lineTotal;
            }


            if ($addToSubTotal > 0) {
                $order->sub_total += $addToSubTotal;
                $order->total_amount = $order->sub_total + $order->tax_amount - $order->discount_amount;
                $order->save();
            }
        });

        session()->flash('success', 'KOT updated & sent to kitchen!');
    }
}
