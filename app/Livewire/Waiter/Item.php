<?php

namespace App\Livewire\Waiter;

use App\Models\{Restaurant, Table, Order, OrderItem, Kot, KOTItem, Payment, RestaurantPaymentLog, PaymentGroup, Addon, Customer, SalesSummaries};
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\{DB, Auth};
use App\Enums\{OrderType, PaymentMethod};
use Illuminate\Validation\Rules\Enum;
use Illuminate\Support\Facades\Log;
use App\Traits\TransactionTrait;

class Item extends Component
{
    use TransactionTrait;

    public $items,
        $categories,
        $selectedCategory = null,
        $table_id,
        $search = '';
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
    public bool $showPriceModal = false;
    public float|string $priceInput = '';
    public string|null $currentPriceKey = null;
    public string|null $priceItemName = null;
    public float $originalPrice = 0;
    public string $discountType = 'percentage';
    public float|string $discountValue = 0;
    public float $serviceCharge = 0;
    public bool $showCartDetailModal = false;
    public bool $showRemoveModal = false;
    public string|null $removeReason = null;
    public string|null $removeKey = null;
    public bool $showCustomerModal = false;
    public string $followupCustomer_name = '';
    public string $followupCustomer_mobile = '';
    public string $followupCustomer_email = '';
    public string $customer_dob = '';
    public string $customer_anniversary = '';
    public string $cartDiscountType = 'percentage';
    public float|string $cartDiscountValue = 0;


    #[Layout('components.layouts.resturant.app')]
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
        $this->items = $table->restaurant
            ->items()
            ->where('is_active', 0)
            ->with(['variants', 'discounts'])
            ->get();
        $this->categories = $this->items->pluck('category')->where('is_active', 0)->unique('id')->values();
        $this->orderTypes = collect(OrderType::cases())->mapWithKeys(fn($c) => [$c->value => $c->label()])->toArray();
        $this->paymentMethods = collect(PaymentMethod::cases())->mapWithKeys(fn($case) => [$case->value => $case->label()])->toArray();
        $this->splits = [['method' => '', 'amount' => 0]];
        $this->editMode = request()->query('mode') === 'edit';

        if ($this->editMode) {
            $this->loadEditModeData($table_id);
        }
    }

    protected function loadEditModeData($table_id)
    {
        $order = Order::where('table_id', $table_id)->where('status', 'pending')->first();

        $kots = Kot::where('order_id', $order->id)->where('status', 'pending')->orderBy('created_at')->get();

        $coustomer = Customer::where('order_id', $order->id)->first();

        if ($coustomer) {
            $this->followupCustomer_name = $coustomer->name;
            $this->followupCustomer_mobile = $coustomer->mobile;
            $this->followupCustomer_email = $coustomer->email;
            $this->customer_dob = $coustomer->dob->format('Y-m-d');
            $this->customer_anniversary = $coustomer->anniversary->format('Y-m-d');
        }

        foreach ($kots as $kot) {
            $this->kotId = $kot->kot_number;
            $this->kotTime = $kot->created_at;

            foreach ($kot->items as $kotItem) {
                $key = $kotItem->variant_id ? 'v' . $kotItem->variant_id : $kotItem->item_id;
                $this->originalKotItemKeys[] = $key;

                $orderItem = OrderItem::where('order_id', $order->id)->where('item_id', $kotItem->item_id)->when($kotItem->variant_id, fn($q) => $q->where('variant_id', $kotItem->variant_id))->latest()->first();

                $price = $orderItem?->final_price ?? ($kotItem->variant_id ? $kotItem->item->price + $kotItem->variant->price : $kotItem->item->price);

                $name = $kotItem->variant_id ? $kotItem->item->name . ' (' . $kotItem->variant->name . ')' : $kotItem->item->name;

                if (!isset($this->cart[$key])) {
                    $this->cart[$key] = [
                        'id' => $key,
                        'item_id' => $kotItem->item_id,
                        'name' => $name,
                        'price' => $price,
                        'qty' => $kotItem->quantity,
                        'note' => $kotItem->special_notes ?? '',
                        'kot_number' => $kot->kot_number,
                        'kot_time' => $kot->created_at->format('H:i'),
                    ];
                }
            }
        }

        if ($order) {
            $this->order_type = $order->order_type;
        }
    }

    public function getFilteredItems()
    {
        $collection = $this->selectedCategory ? $this->items->where('category_id', $this->selectedCategory) : $this->items;

        if ($this->search !== '') {
            $searchLower = str($this->search)->lower();
            $collection = $collection->filter(function ($i) use ($searchLower) {
                return str($i->name)->lower()->contains($searchLower) ||
                    str($i->code ?? '')
                        ->lower()
                        ->contains($searchLower) ||
                    str($i->short_name ?? '')
                        ->lower()
                        ->contains($searchLower);
            });
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
        $subtotal = collect($this->cart)->sum(fn($item) => $item['qty'] * $item['price']);

        // Calculate cart discount
        $discount = 0;
        if ($this->cartDiscountType === 'percentage') {
            $discount = ($subtotal * floatval($this->cartDiscountValue)) / 100;
        } elseif ($this->cartDiscountType === 'fixed') {
            $discount = floatval($this->cartDiscountValue);
        }

        $discount = min($discount, $subtotal); // ensure not more than subtotal
        $service = $this->serviceCharge ?? 0;

        return $subtotal - $discount + $service;
    }


    public function getSubtotal()
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
            'base_price' => $item->price,
            'variant_price' => 0,
            'addons_price' => 0,
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
                $this->addToCart($item->id, $item->name, $baseDiscountedPrice, $item->price, 0, 0);
            }
        }
    }

    public function addSelectedVariant()
    {
        $addonsPrice = collect($this->selectedAddons)->sum(fn($id) => collect($this->addonOptions)->firstWhere('id', $id)['price']);

        if ($this->selectedVariantId) {
            $v = collect($this->variantOptions)->firstWhere('id', $this->selectedVariantId);
            $key = 'v' . $v['id'];
            $this->addToCart($key, $v['combined_name'], $v['combined_price'] + $addonsPrice, $this->currentItem['base_price'], $v['variant_price'], $addonsPrice);
            $this->cart[$key]['item_id'] = $v['item_id'];
            $this->cart[$key]['addons'] = $this->selectedAddons;
        } elseif ($this->currentItem) {
            $key = $this->currentItem['id'];
            $this->addToCart($key, $this->currentItem['name'], $this->currentItem['price'] + $addonsPrice, $this->currentItem['base_price'], 0, $addonsPrice);
            $this->cart[$key]['addons'] = $this->selectedAddons;
        }

        $this->reset(['showVariantModal', 'variantOptions', 'selectedVariantId', 'currentItem', 'addonOptions', 'selectedAddons']);
    }

    private function addToCart($key, $name, $price, $basePrice = 0, $variantPrice = 0, $addonsPrice = 0)
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
                'base_price' => $basePrice,
                'variant_price' => $variantPrice,
                'addons_price' => $addonsPrice,
            ];
            return;
        }

        $existingKey = $this->editMode ? collect(array_keys($this->cart))->first(fn($k) => $k === $key || str_starts_with($k, $key . '-new')) : $key;

        if ($existingKey && isset($this->cart[$existingKey])) {
            $this->cart[$existingKey]['qty']++;
        } else {
            $this->cart[$key] = [
                'id' => $key,
                'name' => $name,
                'price' => $price,
                'qty' => 1,
                'note' => '',
                'base_price' => $basePrice,
                'variant_price' => $variantPrice,
                'addons_price' => $addonsPrice,
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

        $nonZeroItems = collect($this->cart)->filter(fn($item) => $item['qty'] > 0)->count();

        if ($nonZeroItems <= 1) {
            session()->flash('error', 'Cannot remove the last item from the cart.');
            return;
        }

        if ($this->editMode && in_array($key, $this->originalKotItemKeys)) {
            $this->removeKey = $key;
            $this->showRemoveModal = true;
            return;
        }

        unset($this->cart[$key]);
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

    public function openPriceModal($key)
    {
        if (!isset($this->cart[$key])) {
            return;
        }

        $this->currentPriceKey = $key;
        $item = $this->cart[$key];

        $editablePrice = $item['base_price'] ?? $item['price'];

        $this->priceInput = $editablePrice;
        $this->originalPrice = $editablePrice;
        $this->priceItemName = $item['name'];
        $this->discountType = 'percentage';
        $this->discountValue = $this->discountValue ?? 0;
        $this->showPriceModal = true;
    }

    public function updatedDiscountValue()
    {
        $this->recalculateDiscountedPrice();
    }

    public function updatedDiscountType()
    {
        $this->recalculateDiscountedPrice();
    }

    private function recalculateDiscountedPrice()
    {
        $original = $this->originalPrice;
        $discount = floatval($this->discountValue ?? 0);

        if ($this->discountType === 'percentage') {
            $final = max($original - ($original * $discount) / 100, 0);
        } elseif ($this->discountType === 'fixed') {
            $final = max($original - $discount, 0);
        } else {
            $final = $original;
        }

        $this->priceInput = number_format($final, 2, '.', '');
    }

    public function savePrice()
    {
        if ($this->currentPriceKey && isset($this->cart[$this->currentPriceKey])) {
            $price = floatval($this->priceInput);
            $addonPrice = $this->cart[$this->currentPriceKey]['addons_price'] ?? 0;
            $variantPrice = $this->cart[$this->currentPriceKey]['variant_price'] ?? 0;

            if ($price >= 0) {
                $this->cart[$this->currentPriceKey]['base_price'] = $price;
                $this->cart[$this->currentPriceKey]['price'] = $price + $variantPrice + $addonPrice;
                $this->cart[$this->currentPriceKey]['discount_type'] = $this->discountType;
                $this->cart[$this->currentPriceKey]['discount_value'] = floatval($this->discountValue);
            }
        }
        $this->showPriceModal = false;
        // $this->reset(['showPriceModal', 'priceInpu t', 'currentPriceKey', 'originalPrice', 'priceItemName', 'discountType', 'discountValue']);
    }

    public function selectOrderType(string $type)
    {
        $this->order_type = $type;
    }

    protected function createOrderAndKot($print = false)
    {
        return DB::transaction(function () use ($print) {
            if (auth()->user()->restaurant_id) {
                $restaurantId = auth()->user()->restaurant_id;
            } else {
                $restaurantId = Restaurant::where('user_id', auth()->id())->value('id');
            }
            $subTotal = $this->getCartTotal();

            $order = Order::create([
                'restaurant_id' => $restaurantId,
                'table_id' => $this->table_id,
                'user_id' => Auth::id(),
                'order_type' => 'dine_in',
                'status' => 'pending',
                'sub_total' => $subTotal,
                'discount_amount' => 0,
                'total_amount' => $subTotal,
                'tax_amount' => 0,
                'service_charge' => $this->serviceCharge,
            ]);

            $kot = Kot::create([
                'table_id' => $this->table_id,
                'order_id' => $order->id,
                'status' => 'pending',
                'printed_at' => $print ? now() : null,
            ]);

            Table::find($this->table_id)->update(['status' => 'occupied']);

            foreach ($this->cart as $row) {
                $variantId = str_starts_with($row['id'], 'v') ? (int) substr($row['id'], 1) : null;
                $baseItemId = $variantId ? $row['item_id'] : $row['id'];

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $baseItemId,
                    'variant_id' => $variantId,
                    'quantity' => $row['qty'],
                    'base_price' => $row['price'],
                    'discount_amount' => 0,
                    'total_price' => $row['qty'] * $row['price'],
                    'special_notes' => $row['note'] ?? null,
                    'status' => 'pending',
                    'discount_type' => $row['discount_type'] ?? null,
                    'discount_value' => $row['discount_value'] ?? 0,
                    'final_price' => $row['price'],
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

        if ($this->order_type === 'takeaway') {
            $this->save();
            return;
        }

        $this->createOrderAndKot();
        $this->reset(['cart', 'showVariantModal']);
        return redirect()->route('restaurant.waiter.dashboard')->with('success', 'Order placed!');
    }

    public function placeOrderAndPrint()
    {
        if (empty($this->cart)) {
            $this->addError('cart', 'Cart is empty!');
            return;
        }

        if ($this->editMode) {
            $this->updateOrder();

            $kot = Kot::where('table_id', $this->table_id)->where('status', 'pending')->latest()->first();

            if ($kot) {
                $kot->update(['printed_at' => now()]);
                $this->dispatch('printKot', kotId: $kot->id);
            }

            return redirect()->route('restaurant.waiter.dashboard')->with('success', 'KOT updated & printed!');
        }

        $this->createOrderAndKot(true);
        $this->reset(['cart', 'showVariantModal', 'noteInput', 'currentNoteKey']);

        return redirect()->route('restaurant.waiter.dashboard')->with('success', 'Order placed & KOT printed!');
    }

    public function updateOrder()
    {
        if (empty($this->cart)) {
            $this->addError('cart', 'Cart is empty!');
            return;
        }

        DB::transaction(function () {
            $order = Order::where('table_id', $this->table_id)->where('status', 'pending')->latest()->firstOrFail();
            $kot = Kot::create([
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
                $baseItemId = $variantId ? $row['item_id'] : (int) preg_replace('/-new\d*/', '', $row['id']);
                $lineTotal = $row['qty'] * $row['price'];

                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'item_id' => $baseItemId,
                    'variant_id' => $variantId,
                    'quantity' => $row['qty'],
                    'base_price' => $row['price'],
                    'discount_amount' => 0,
                    'total_price' => $row['qty'] * $row['price'],
                    'special_notes' => $row['note'] ?? null,
                    'status' => 'pending',
                    'discount_type' => $row['discount_type'] ?? null,
                    'discount_value' => $row['discount_value'] ?? 0,
                    'final_price' => $row['price'],
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
        return redirect()->route('restaurant.waiter.dashboard');
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

        $order = Order::where('table_id', $this->table_id)->where('status', 'pending')->latest()->first();

        if (!$order) {
            $this->createOrderAndKot();
            $order = Order::where('table_id', $this->table_id)->where('status', 'pending')->latest()->first();
        } else {
            $newItemsExist = collect($this->cart)
                ->reject(function ($item, $key) {
                    return in_array($key, $this->originalKotItemKeys);
                })
                ->isNotEmpty();

            if ($newItemsExist) {
                $this->updateOrder();
            }
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
            $kots = Kot::where('order_id', $order->id)->where('status', 'pending')->get();

            foreach ($kots as $kot) {
                $kot->update(['status' => 'ready']);
                $kot->items()->update(['status' => 'served']);
            }

            $orderItems = OrderItem::where('order_id', $order->id)->get();
            $table = Table::findOrFail($this->table_id);

            $order->update(['status' => 'served']);
            $orderItems->each(fn($item) => $item->update(['status' => 'served']));
            $table->update(['status' => 'available']);
            $amount = $this->getCartTotal();

            Payment::create([
                'restaurant_id' => $order->restaurant_id,
                'order_id' => $order->id,
                'amount' => $amount,
                'method' => $this->paymentMethod,
            ]);
            $this->totalSale($order->restaurant_id, $order->total_amount);
        }

        return redirect()->route('restaurant.waiter.dashboard')->with('success', 'Order Payment Complete!');
    }

    public function saveAndPrint()
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

        $order = Order::where('table_id', $this->table_id)->where('status', 'pending')->latest()->first();

        if (!$order) {
            $this->createOrderAndKot();
            $order = Order::where('table_id', $this->table_id)->where('status', 'pending')->latest()->first();
        } else {
            $newItemsExist = collect($this->cart)
                ->reject(function ($item, $key) {
                    return in_array($key, $this->originalKotItemKeys);
                })
                ->isNotEmpty();

            if ($newItemsExist) {
                $this->updateOrder();
            }
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
            $kots = Kot::where('order_id', $order->id)->where('status', 'pending')->get();

            foreach ($kots as $kot) {
                $kot->update(['status' => 'ready']);
                $kot->items()->update(['status' => 'served']);
            }

            $orderItems = OrderItem::where('order_id', $order->id)->get();
            $table = Table::findOrFail($this->table_id);

            $order->update(['status' => 'served']);
            $orderItems->each(fn($item) => $item->update(['status' => 'served']));
            $table->update(['status' => 'available']);
            $amount = $this->getCartTotal();

            Payment::create([
                'restaurant_id' => $order->restaurant_id,
                'order_id' => $order->id,
                'amount' => $amount,
                'method' => $this->paymentMethod,
            ]);
            $this->totalSale($order->restaurant_id, $order->total_amount);
        }

        $this->dispatch('printBill', billId: $order->id);
        return redirect()->route('restaurant.waiter.dashboard')->with('success', 'Order Payment Complete!');
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
        $order = Order::where('table_id', $this->table_id)->where('status', 'pending')->latest()->firstOrFail();

        $kots = Kot::where('order_id', $order->id)->where('status', 'pending')->get();

        if (auth()->user()->restaurant_id) {
            $restaurantId = auth()->user()->restaurant_id;
        } else {
            $restaurantId = Restaurant::where('user_id', auth()->id())->value('id');
        }

        $orderItems = OrderItem::where('order_id', $order->id)->get();

        $kotItems = KOTItem::whereIn('kot_id', $kots->pluck('id'))->get();

        $this->validate([
            'customerName' => 'nullable|string|max:100',
            'mobile' => 'nullable|string|max:20',
            'splits.*.method' => ['required', new Enum(PaymentMethod::class)],
            'splits.*.amount' => 'required|numeric|min:0.01',
        ]);

        $total = round(collect($this->splits)->sum('amount'), 2);
        $orderTotal = round($order->total_amount, 2);

        if (bccomp($total, $orderTotal, 2) !== 0) {
            $diff = number_format(abs($orderTotal - $total), 2);
            session()->flash('error', "Split amounts must match the order total (₹{$orderTotal}). Difference: ₹{$diff}");
            return;
        }

        $payment = Payment::create([
            'restaurant_id' => $order->restaurant_id,
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'method' => 'part',
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
            $table = Table::findOrFail($this->table_id);

            $table->update([
                'status' => 'available',
            ]);

            $order->update([
                'customer_name' => $this->customerName,
                'mobile' => $this->mobile,
                'status' => 'served',
            ]);

            $orderItems->each(function ($item) use ($order) {
                $item->update(['status' => 'served']);
            });

            $kots->each(function ($kot) {
                $kot->update(['status' => 'ready']);
            });

            $kotItems->each(function ($item) {
                $item->update(['status' => 'served']);
            });
            $this->totalSale($order->restaurant_id, $order->total_amount);
        }

        $this->reset(['splits', 'paymentMethod', 'showSplitModal', 'customerName', 'mobile']);

        return redirect()->route('restaurant.waiter.dashboard')->with('success', 'Order split payment recorded!');
    }

    public function confirmDuoPayment()
    {
        $this->validate([
            'duoCustomerName' => 'required|string|max:100',
            'duoMobile' => 'required|string|max:20',
            'duoAmount' => 'nullable|numeric|min:0',
            'duoMethod' => ['nullable', function ($attribute, $value, $fail) {
                if (!is_null($value) && !PaymentMethod::tryFrom($value)) {
                    $fail("The selected payment method is invalid.");
                }
            }],
            'duoIssue' => 'nullable|string|max:255',
        ]);

        $order = Order::where('table_id', $this->table_id)->where('status', 'pending')->latest()->first();
        if (auth()->user()->restaurant_id) {
            $restaurantId = auth()->user()->restaurant_id;
        } else {
            $restaurantId = Restaurant::where('user_id', auth()->id())->value('id');
        }

        if (!$order) {
            $order = $this->createOrderAndKot();
        }

        $pendingKOTs = Kot::where('table_id', $this->table_id)->where('order_id', $order->id)->where('status', 'pending')->get();

        foreach ($pendingKOTs as $kot) {
            $kotItems = KOTItem::where('kot_id', $kot->id)->get();
            $kot->update(['status' => 'ready']);
            $kotItems->each(fn($item) => $item->update(['status' => 'served']));
        }

        $orderItems = OrderItem::where('order_id', $order->id)->get();
        $table = Table::findOrFail($this->table_id);

        $order->update([
            'customer_name' => $this->duoCustomerName,
            'mobile' => $this->duoMobile,
            'status' => 'served',
        ]);

        $orderItems->each(fn($item) => $item->update(['status' => 'served']));
        $table->update(['status' => 'available']);

        if ($this->duoAmount > $order->total_amount) {
            session()->flash('error', 'Paid amount cannot exceed total order amount.');
            return;
        }

        $payment = Payment::create([
            'restaurant_id' => $order->restaurant_id,
            'order_id' => $order->id,
            'amount' => $order->total_amount,
            'method' => 'duo',
        ]);
        $this->totalSale($order->restaurant_id, $order->total_amount);

        $remainingAmount = $order->total_amount - $this->duoAmount;

        RestaurantPaymentLog::create([
            'restaurant_id' => $restaurantId,
            'payment_id' => $payment->id,
            'order_id' => $order->id,
            'customer_name' => $this->duoCustomerName,
            'mobile' => $this->duoMobile,
            'paid_amount' => $this->duoAmount,
            'amount' => $remainingAmount,
            'method' => $this->duoMethod ?: 'cash',
            'issue' => $this->duoIssue,
        ]);

        $this->reset(['showDuoPaymentModal', 'duoCustomerName', 'duoMobile', 'duoAmount', 'duoMethod', 'duoIssue', 'paymentMethod']);

        return redirect()->route('restaurant.waiter.dashboard')->with('success', 'Duo Payment Completed!');
    }

    public function confirmRemove()
    {
        if (!$this->removeKey || !isset($this->cart[$this->removeKey])) {
            return;
        }

        $key = $this->removeKey;
        $reason = $this->removeReason;

        DB::transaction(function () use ($key, $reason) {
            $variantId = str_starts_with($key, 'v') ? (int) substr($key, 1) : null;
            $itemId = $variantId ? $this->cart[$key]['item_id'] : (int) $this->cart[$key]['item_id'];

            $order = Order::where('table_id', $this->table_id)->where('status', 'pending')->latest()->first();
            $kot = Kot::where('order_id', $order->id)->where('status', 'pending')->latest()->first();

            OrderItem::where('order_id', $order->id)
                ->where('item_id', $itemId)
                ->when($variantId, fn($q) => $q->where('variant_id', $variantId))
                ->update([
                    'delete_reason' => $reason,
                    'deleted_at' => now(),
                ]);

            KOTItem::where('kot_id', $kot->id)->where('item_id', $itemId)->when($variantId, fn($q) => $q->where('variant_id', $variantId))->delete();
        });

        unset($this->cart[$key]);
        $this->showRemoveModal = false;
        $this->reset(['showRemoveModal', 'removeKey', 'removeReason']);
    }

    public function openCustomerModal()
    {
        $this->resetValidation();
        $this->showCustomerModal = true;
    }

    public function saveCustomer()
    {
        $this->validate([
            'followupCustomer_name' => 'required|string|max:100',
            'followupCustomer_mobile' => 'required|string|max:20',
            'followupCustomer_email' => 'nullable|email',
            'customer_dob' => 'nullable|date',
            'customer_anniversary' => 'nullable|date',
        ]);

        $order = Order::where('table_id', $this->table_id)->where('status', 'pending')->latest()->firstOrFail();

        $order->update([
            'customer_name' => $this->followupCustomer_name,
            'mobile' => $this->followupCustomer_mobile,
        ]);

        $coustomer = Customer::where('order_id', $order->id)->first();
        if (!$coustomer) {
            $coustomer->create([
                'order_id' => $order->id,
                'name' => $this->followupCustomer_name,
                'mobile' => $this->followupCustomer_mobile,
                'email' => $this->followupCustomer_email,
                'dob' => $this->customer_dob,
                'anniversary' => $this->customer_anniversary,
                'restaurant_id' => auth()->user()->restaurant_id,
            ]);

            $order->update([
                'customer_id' => $coustomer->id,
            ]);
        }

        $this->showCustomerModal = false;

        session()->flash('success', 'Customer added and linked to order!');
    }
}
