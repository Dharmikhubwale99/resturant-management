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

    public $variantOptions = [];

    public $selectedVariantId = null;

    public $showNoteModal = false;

    public $noteInput = '';

    public $currentNoteKey = null;

    public $orderTypes = [];

    public $order_type;

    public $showTableList = false;
    public $occupiedTables = [];
    public $selectedTable = null;
    public $ordersForTable = [];

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

        if ($item->variants->isNotEmpty()) {
            $this->variantOptions = $item->variants
                ->map(
                    fn($v) => [
                        'id' => $v->id,
                        'item_id' => $item->id,
                        'combined_name' => $item->name . ' (' . $v->name . ')',
                        'combined_price' => $item->price + $v->price,
                        'variant_name' => $v->name,
                        'variant_price' => $v->price,
                    ],
                )
                ->toArray();

            $this->selectedVariantId = $this->variantOptions[0]['id'] ?? null;
            $this->showVariantModal = true;
        } else {
            $this->addToCart($item->id, $item->name, $item->price);
        }
    }

    public function addSelectedVariant()
    {
        $v = collect($this->variantOptions)->firstWhere('id', $this->selectedVariantId);
        if (!$v) {
            return;
        }

        $key = 'v' . $v['id'];

        // create row if not exists
        $this->addToCart($key, $v['combined_name'], $v['combined_price']);

        $this->cart[$key]['item_id'] = $v['item_id'];

        $this->showVariantModal = false;
    }

    private function addToCart($key, $name, $price)
    {
        if (isset($this->cart[$key])) {
            $this->cart[$key]['qty']++;
        } else {
            $this->cart[$key] = [
                'id' => $key,
                'name' => $name,
                'price' => $price,
                'qty' => 1,
                'note' => '',
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
        if (--$this->cart[$key]['qty'] < 1) {
            unset($this->cart[$key]);
        }
    }

    public function remove($key)
    {
        unset($this->cart[$key]);
    }

    public function updateQty($key, $qty)
    {
        $qty = (int) $qty;
        if ($qty < 1) {
            unset($this->cart[$key]);
            return;
        }
        if (isset($this->cart[$key])) {
            $this->cart[$key]['qty'] = $qty;
        }
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

    public function placeOrder()
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

            // $order = Order::create([
            //     'restaurant_id' => $restaurantId,
            //     'table_id' => $this->table_id,
            //     'user_id' => Auth::id(),
            //     'order_type' => $this->order_type,
            //     'status' => 'pending',
            //     'sub_total' => $subTotal,
            //     'discount_amount' => $discountAmount,
            //     'tax_amount' => $taxAmount,
            //     'total_amount' => $totalAmount,
            // ]);

            $kot = Kot::create([
                'table_id' => $this->table_id,
                'order_id' => null,
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

                // OrderItem::create([
                //     'order_id' => $order->id,
                //     'item_id' => $baseItemId,
                //     'variant_id' => $variantId,
                //     'quantity' => $row['qty'],
                //     'base_price' => $row['price'],
                //     'discount_amount' => 0,
                //     'total_price' => $row['qty'] * $row['price'],
                //     'special_notes' => $row['note'] ?? null,
                //     'status' => 'pending',
                // ]);

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

        $kotId = null;

        DB::transaction(function () use (&$kotId) {
            $restaurantId = auth()->user()?->restaurant_id;

            $subTotal = $this->getCartTotal();
            $discountAmount = 0;
            $taxAmount = 0;
            $totalAmount = $subTotal + $taxAmount - $discountAmount;

            $kot = Kot::create([
                'table_id' => $this->table_id,
                'order_id' => null,
                'status' => 'pending',
                'printed_at' => now(),
            ]);

            $kotId = $kot->id;

            Table::findOrFail($this->table_id)->update(['status' => 'occupied']);

            foreach ($this->cart as $row) {
                $variantId = str_starts_with($row['id'], 'v') ? (int) substr($row['id'], 1) : null;

                $baseItemId = $variantId ? $row['item_id'] : $row['id'];

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

        $this->dispatch('printKot', kotId: $kotId);
    }

    public function showTables()
    {
        $this->occupiedTables = Table::where('restaurant_id', auth()->user()->restaurant_id)
            ->where('status', 'occupied')
            ->get();
        $this->showTableList = true;
    }

    public function selectTable($tableId)
    {
        $this->selectedTable = Table::with('orders')->findOrFail($tableId);
        $this->ordersForTable = $this->selectedTable->orders()->latest()->get();
        $this->showTableList = false;
    }
}
