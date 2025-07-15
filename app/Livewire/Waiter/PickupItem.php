<?php

namespace App\Livewire\Waiter;

use App\Models\{Table, Order, OrderItem, KOT, KOTItem, Payment, RestaurantPaymentLog, PaymentGroup, Addon};
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\{DB, Auth};
use App\Enums\{OrderType, PaymentMethod};
use Illuminate\Validation\Rules\Enum;

class PickupItem extends Component
{
    public $items, $order,
        $categories,
        $selectedCategory = null,
        $table_id,
        $searchCode = '',
        $search = '',
        $searchShortName = '';
    public $cart = [],
        $showVariantModal = false,
        $currentItem = null,
        $variantOptions = [];
    public $selectedVariantId = null,
        $showNoteModal = false,
        $noteInput = '',
        $currentNoteKey = null;
    public $orderTypes = [],
        $order_type,
        $kotId,
        $kotTime;
    public $occupiedTables = [],
        $ordersForTable = [];
    public array $originalKotItemKeys = [];
    public bool $editMode = false;
    public string $paymentMethod = '';
    public $paymentMethods = [];
    public bool $showSplitModal = false;
    public array $splits = [];
    public string $customerName = '';
    public string $mobile = '';
    public bool $showDuoPaymentModal = false;
    public string $duoCustomerName = '';
    public string $duoMobile = '';
    public float $duoAmount = 0;
    public string $duoIssue = '';
    public string $duoMethod = '';
    public $addonOptions = [];
    public $selectedAddons = [];
    public $orderId;

    #[Layout('components.layouts.waiter.app')]
    public function render()
    {
        return view('livewire.waiter.pickup-item', [
            'filteredItems' => $this->getFilteredItems(),
            'cartItems' => $this->cart,
            'cartTotal' => $this->getCartTotal(),
        ]);
    }

    public function mount($id)
    {
        $this->order = Order::findOrFail($id);
        $this->orderId = $id;
        $this->editMode = request()->query('mode') === 'edit';

        $this->items = $this->order->restaurant
            ->items()
            ->with(['variants', 'discounts'])
            ->get();
        $this->categories = $this->items->pluck('category')->unique('id')->values();
        $this->orderTypes = collect(OrderType::cases())->mapWithKeys(fn($c) => [$c->value => $c->label()])->toArray();
        $this->paymentMethods = collect(PaymentMethod::cases())->mapWithKeys(fn($case) => [$case->value => $case->label()])->toArray();
        $this->splits = [['method' => '', 'amount' => 0]];

        if ($this->editMode) {
            $this->loadEditModeData($id);
        }
    }

    protected function loadEditModeData($id)
    {
        $latestKot = KOT::where('order_id', $id)->where('status', 'pending')->first();
        $this->kotId = $latestKot?->kot_number;
        $this->kotTime = $latestKot?->created_at;

        $order = Order::where('id', $id)->where('status', 'pending')->first();
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
        if ($order) {
            $this->order_type = $order->order_type;
        }
    }

    public function getFilteredItems()
    {
        $collection = $this->selectedCategory ? $this->items->where('category_id', $this->selectedCategory) : $this->items;

        if ($this->search !== '') {
            $collection = $collection->filter(fn($i) =>
                str($i->name)->lower()->contains(str($this->search)->lower())
            );
        }

        if ($this->searchCode !== '') {
            $collection = $collection->filter(fn($i) =>
                str($i->code ?? '')->lower()->contains(str($this->searchCode)->lower())
            );
        }

        if ($this->searchShortName !== '') {
            $collection = $collection->filter(fn($i) =>
                str($i->short_name ?? '')->lower()->contains(str($this->searchShortName)->lower())
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

    private function applyDiscount($basePrice, $discount)
    {
        if (!$discount) {
            return $basePrice;
        }

        if ($discount->type === 'percentage') {
            return max($basePrice - ($basePrice * $discount->value) / 100, 0);
        } elseif ($discount->type === 'fixed') {
            return max($basePrice - $discount->minimum_amount, 0);
        }

        return $basePrice;
    }

    public function itemClicked($itemId)
    {
        if (!($item = $this->items->find($itemId))) {
            return;
        }

        $this->reset(['variantOptions', 'selectedVariantId', 'addonOptions', 'selectedAddons', 'currentItem', 'showVariantModal']);

        $discount = $item->discounts->where('is_active', 0)->first();

        $baseDiscountedPrice = $this->applyDiscount($item->price, $discount);

        $this->currentItem = [
            'id' => $item->id,
            'name' => $item->name,
            'price' => $baseDiscountedPrice,
        ];

        $this->addonOptions = $item->addons
            ->map(function ($addon) {
                return [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'price' => $addon->price,
                ];
            })
            ->toArray();

        if ($item->variants->isNotEmpty()) {
            $this->variantOptions = $item->variants
                ->map(function ($v) use ($item, $discount) {
                    $combinedBasePrice = $item->price + $v->price;
                    $combinedDiscountedPrice = $this->applyDiscount($combinedBasePrice, $discount);

                    return [
                        'id' => $v->id,
                        'item_id' => $item->id,
                        'combined_name' => $item->name . ' (' . $v->name . ')',
                        'combined_price' => $combinedDiscountedPrice,
                        'variant_price' => $v->price,
                        'variant_name' => $v->name,
                    ];
                })
                ->toArray();

            $this->selectedVariantId = null;
            $this->showVariantModal = true;
        } else {
            if (count($this->addonOptions)) {
                $this->showVariantModal = true;
            } else {
                $this->addToCart($item->id, $item->name, $baseDiscountedPrice);
            }
        }
    }

    public function addSelectedVariant()
    {
        $addonsPrice = collect($this->selectedAddons)->sum(fn($id) => collect($this->addonOptions)->firstWhere('id', $id)['price']);

        if ($this->selectedVariantId) {
            $v = collect($this->variantOptions)->firstWhere('id', $this->selectedVariantId);
            $key = 'v' . $v['id'];
            $this->addToCart($key, $v['combined_name'], $v['combined_price'] + $addonsPrice);
            $this->cart[$key]['item_id'] = $v['item_id'];
            $this->cart[$key]['addons'] = $this->selectedAddons;
        } elseif ($this->currentItem) {
            $key = $this->currentItem['id'];
            $this->addToCart($key, $this->currentItem['name'], $this->currentItem['price'] + $addonsPrice);
            $this->cart[$key]['addons'] = $this->selectedAddons;
        }

        $this->reset(['showVariantModal', 'variantOptions', 'selectedVariantId', 'currentItem', 'addonOptions', 'selectedAddons']);
    }

    private function addToCart($key, $name, $price)
    {
        if ($this->editMode && in_array($key, $this->originalKotItemKeys)) {
            $existingNewKey = collect(array_keys($this->cart))->first(fn($k) => str_starts_with($k, $key . '-new'));
            if ($existingNewKey) {
                $this->cart[$existingNewKey]['qty']++;
                return;
            }
            $newKey = $key . '-new';
            $this->cart[$newKey] = [
                'id' => $newKey,
                'item_id' => str_starts_with($key, 'v') ? null : $key,
                'name' => $name,
                'price' => $price,
                'qty' => 1,
                'note' => '',
            ];
            return;
        }

        $existingKey = $this->editMode ? collect(array_keys($this->cart))->first(fn($k) => $k === $key || str_starts_with($k, $key . '-new')) : $key;

        if ($existingKey && isset($this->cart[$existingKey])) {
            $this->cart[$existingKey]['qty']++;
        } else {
            $this->cart[$key] = ['id' => $key, 'name' => $name, 'price' => $price, 'qty' => 1, 'note' => ''];
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
        if (!isset($this->cart[$key])) {
            return;
        }

        $qty = (int) $qty;

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
        $this->reset(['showNoteModal', 'noteInput', 'currentNoteKey']);
    }

    public function selectOrderType(string $type)
    {
        $this->order_type = $type;
    }

    protected function createOrderAndKot($print = false)
    {
        return DB::transaction(function () use ($print) {
            $restaurantId = Auth::user()->restaurant_id;
            $subTotal = $this->getCartTotal();

            $order = Order::findOrFail($this->orderId);
            $order->update([
                'restaurant_id' => $restaurantId,
                'user_id' => Auth::id(),
                'status' => 'pending',
                'sub_total' => $subTotal,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => $subTotal,
            ]);

            $kot = KOT::create([
                'order_id' => $this->order->id,
                'status' => 'pending',
                'printed_at' => $print ? now() : null,
            ]);

            foreach ($this->cart as $row) {
                $variantId = str_starts_with($row['id'], 'v') ? (int) substr($row['id'], 1) : null;
                $baseItemId = $variantId ? $row['item_id'] : $row['id'];

                $orderItem = OrderItem::create([
                    'order_id' => $this->order->id,
                    'item_id' => $baseItemId,
                    'variant_id' => $variantId,
                    'quantity' => $row['qty'],
                    'base_price' => $row['price'],
                    'discount_amount' => 0,
                    'total_price' => $row['qty'] * $row['price'],
                    'special_notes' => $row['note'] ?? null,
                    'status' => 'pending',
                ]);

                $kotItem = KOTItem::create([
                    'kot_id' => $kot->id,
                    'item_id' => $baseItemId,
                    'variant_id' => $variantId,
                    'quantity' => $row['qty'],
                    'status' => 'pending',
                    'special_notes' => $row['note'] ?? null,
                ]);

                if (!empty($row['addons'])) {
                    foreach ($row['addons'] as $addonId) {
                        $addon = Addon::find($addonId);
                        if ($addon) {
                            $orderItem->addons()->attach($addon->id, ['price' => $addon->price]);
                            $kotItem->addons()->attach($addon->id, ['price' => $addon->price]);
                        }
                    }
                }
            }

            if ($print) {
                $this->dispatch('printKot', kotId: $kot->id);
            }
            return $kot;
        });
    }

    public function placeOrder()
    {
        if (empty($this->cart)) {
            $this->addError('cart', 'Cart is empty!');
            return;
        }

        $this->createOrderAndKot();
        $this->reset(['cart', 'showVariantModal']);
        return redirect()->route('waiter.pickup.create')->with('success', 'Order placed!');
    }

    public function placeOrderAndPrint()
    {
        if (empty($this->cart)) {
            $this->addError('cart', 'Cart is empty!');
            return;
        }

        if ($this->editMode) {
            $this->updateOrder();

            $kot = KOT::where('order_id', $this->order)->where('status', 'pending')->latest()->first();

            if ($kot) {
                $kot->update(['printed_at' => now()]);
                $this->dispatch('printKot', kotId: $kot->id);
            }

            return redirect()->route('waiter.dashboard')->with('success', 'KOT updated & printed!');
        }

        $this->createOrderAndKot(true);
        $this->reset(['cart', 'showVariantModal', 'noteInput', 'currentNoteKey']);

        return redirect()->route('waiter.pickup.create')->with('success', 'Order placed & KOT printed!');
    }

    public function updateOrder()
    {


        DB::transaction(function () {
            $order = Order::where('id', $this->orderId)->first();

            $kot = KOT::create([
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
                $baseItemId = $variantId ? $row['item_id'] : (int) preg_replace('/-new\d*/', '', $row['id']);
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
        return redirect()->route('waiter.pickup.create')->with('success', 'Order Payment Complete!');
    }

    public function save()
    {
        $this->validate(
            [
                'paymentMethod' => 'required|in:cash,card,duo,upi,part',
            ],
            [
                'paymentMethod.required' => 'Please choose a payment method.',
                'paymentMethod.in' => 'Invalid payment method selected.',
            ],
        );

        $order = Order::where('id', $this->order)->where('status', 'pending')->latest()->first();

        if (!$order) {
            $order = $this->createOrderAndKot();
        }

        if ($this->paymentMethod === 'part') {
            $this->showSplitModal = true;
            return;
        } elseif ($this->paymentMethod === 'duo') {
            $this->showDuoPaymentModal = true;
            $this->duoAmount = $this->getCartTotal();
            return;
        }

        if ($order) {
            $kot = KOT::where('order_id', $this->order)->where('status', 'pending')->latest()->first();

            if ($kot) {
                $kotItems = KOTItem::where('kot_id', $kot->id)->get();
                $kot->update(['status' => 'ready']);
                $kotItems->each(fn($item) => $item->update(['status' => 'served']));
            }

            $orderItems = OrderItem::where('order_id', $order->id)->get();

            $order->update(['status' => 'served']);
            $orderItems->each(fn($item) => $item->update(['status' => 'served']));
            $amount = $this->getCartTotal();

            Payment::create([
                'order_id' => $order->id,
                'amount' => $amount,
                'method' => $this->paymentMethod,
            ]);
        }

        return redirect()->route('waiter.pickup.create')->with('success', 'Order Payment Complete!');
    }

    public function saveAndPrint()
    {
        $this->validate(
            [
                'paymentMethod' => 'required|in:cash,card,duo,other,part',
            ],
            [
                'paymentMethod.required' => 'Please choose a payment method.',
                'paymentMethod.in' => 'Invalid payment method selected.',
            ],
        );

        $order = Order::where('id', $this->order)->where('status', 'pending')->latest()->first();

        if (!$order) {
            $order = $this->createOrderAndKot();
        }

        if ($this->paymentMethod === 'part') {
            $this->showSplitModal = true;
            return;
        } elseif ($this->paymentMethod === 'duo') {
            $this->showDuoPaymentModal = true;
            $this->duoAmount = $this->getCartTotal();
            return;
        }

        if ($order) {
            $kot = KOT::where('order_id', $this->order)->where('status', 'pending')->latest()->first();

            if ($kot) {
                $kotItems = KOTItem::where('kot_id', $kot->id)->get();
                $kot->update(['status' => 'ready']);
                $kotItems->each(fn($item) => $item->update(['status' => 'served']));
            }

            $orderItems = OrderItem::where('order_id', $order->id)->get();
            $table = Table::findOrFail($this->table_id);

            $order->update(['status' => 'served']);
            $orderItems->each(fn($item) => $item->update(['status' => 'served']));
            $table->update(['status' => 'available']);
            $amount = $this->getCartTotal();

            Payment::create([
                'order_id' => $order->id,
                'amount' => $amount,
                'method' => $this->paymentMethod,
            ]);
        }
        $this->dispatch('printBill', billId: $order->id);
        return redirect()->route('waiter.pickup.create')->with('success', 'Order Payment Complete!');
    }

    public function addSplit()
    {
        $this->splits[] = ['method' => '', 'amount' => null];
    }

    public function removeSplit($index)
    {
        unset($this->splits[$index]);
        $this->splits = array_values($this->splits);
    }

    public function confirmSplit()
    {
        $order = Order::where('id', $this->order)->where('status', 'pending')->latest()->firstOrFail();

        $kot = KOT::where('order_id', $order->id)->where('status', 'pending')->latest()->firstOrFail();

        $restaurantId = Auth::user()->restaurant_id;

        $orderItems = OrderItem::where('order_id', $order->id)->get();

        $kotItems = KOTItem::where('kot_id', $kot->id)->get();

        $this->validate([
            'customerName' => 'nullable|string|max:100',
            'mobile' => 'nullable|string|max:20',
            'splits.*.method' => ['required', new Enum(PaymentMethod::class)],
            'splits.*.amount' => 'required|numeric|min:0.01',
        ]);

        $total = collect($this->splits)->sum('amount');

        if (bccomp($total, $order->total_amount, 2) !== 0) {
            session()->flash('error', 'Split amounts must equal the order total (₹' . number_format($order->total_amount, 2) . ').');
            return;
        }
        $payment = Payment::create([
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'method' => $this->paymentMethod,
        ]);

        foreach ($this->splits as $split) {
            PaymentGroup::create([
                'restaurant_id' => $restaurantId,
                'payment_id' => $payment->id,
                'order_id' => $order->id,
                'customer_name' => $this->customerName,
                'mobile' => $this->mobile,
                'amount' => $split['amount'],
                'method' => $split['method'],
            ]);
        }

        if ($order) {

            $order->update([
                'status' => 'served',
            ]);

            $orderItems->each(function ($item) use ($order) {
                $item->update(['status' => 'served']);
            });

            $kot->update([
                'status' => 'ready',
            ]);

            $kotItems->each(function ($item) use ($kot) {
                $item->update(['status' => 'served']);
            });
        }

        $this->reset(['splits', 'paymentMethod', 'showSplitModal', 'customerName', 'mobile']);

        return redirect()->route('waiter.pickup.create')->with('success', 'Order split payment recorded!');
    }

    public function confirmDuoPayment()
    {
        $this->validate([
            'duoCustomerName' => 'required|string|max:100',
            'duoMobile' => 'required|string|max:20',
            'duoAmount' => 'required|numeric|min:0.01',
            'duoMethod' => ['required', new Enum(PaymentMethod::class)],
            'duoIssue' => 'nullable|string|max:255',
        ]);

        $order = Order::where('id', $this->order)->where('status', 'pending')->latest()->first();

        if (!$order) {
            $order = $this->createOrderAndKot();
        }

        $kot = KOT::where('order_id', $this->order)->where('status', 'pending')->latest()->first();

        if ($kot) {
            $kotItems = KOTItem::where('kot_id', $kot->id)->get();
            $kot->update(['status' => 'ready']);
            $kotItems->each(fn($item) => $item->update(['status' => 'served']));
        }

        $orderItems = OrderItem::where('order_id', $order->id)->get();
        $table = Table::findOrFail($this->table_id);

        $order->update(['status' => 'served']);
        $orderItems->each(fn($item) => $item->update(['status' => 'served']));
        $table->update(['status' => 'available']);

        $payment = Payment::create([
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'method' => 'duo',
        ]);

        $remainingAmount = $order->total_amount - $this->duoAmount;

        RestaurantPaymentLog::create([
            'restaurant_id' => Auth::user()->restaurant_id,
            'payment_id' => $payment->id,
            'order_id' => $order->id,
            'customer_name' => $this->duoCustomerName,
            'mobile' => $this->duoMobile,
            'paid_amount' => $this->duoAmount,
            'amount' => $remainingAmount,
            'method' => $this->duoMethod,
            'issue' => $this->duoIssue,
        ]);

        $this->reset(['showDuoPaymentModal', 'duoCustomerName', 'duoMobile', 'duoAmount', 'duoMethod', 'duoIssue', 'paymentMethod']);

        return redirect()->route('waiter.pickup.create')->with('success', 'Duo Payment Completed!');
    }
}
