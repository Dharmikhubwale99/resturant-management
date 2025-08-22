<?php

namespace App\Livewire\Waiter;

use App\Models\{Restaurant, Table, Order, OrderItem, Kot, KOTItem, Payment, RestaurantPaymentLog, PaymentGroup, Addon, Customer, SalesSummaries, Variant};
use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\{DB, Auth};
use App\Enums\{OrderType, PaymentMethod};
use Illuminate\Validation\Rules\Enum;
use App\Traits\TransactionTrait;

class PickupItem extends Component
{
    use TransactionTrait;
    public $items,
        $order,
        $categories,
        $selectedCategory = null,
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
    public array $originalKotItemKeys = [];
    public bool $editMode = false;
    public string $paymentMethod = '';
    public $paymentMethods = [],
        $orderId;
    public bool $showSplitModal = false,
        $showDuoPaymentModal = false,
        $showCustomerModal = false;
    public array $splits = [];
    public string $customerName = '',
        $mobile = '',
        $duoCustomerName = '',
        $duoMobile = '';
    public float $duoAmount = 0;
    public string $duoIssue = '',
        $duoMethod = '';
    public $addonOptions = [],
        $selectedAddons = [];
    public string $followupCustomer_name = '';
    public string $followupCustomer_mobile = '';
    public string $followupCustomer_email = '';
    public string $customer_dob = '';
    public string $customer_anniversary = '';
    public float $serviceCharge = 0;
    public bool $showRemoveModal = false;
    public string|null $removeKey = null;
    public bool $showPriceModal = false;
    public float|string $priceInput = '';
    public string|null $currentPriceKey = null;
    public string|null $priceItemName = null;
    public float $originalPrice = 0;
    public string $discountType = 'percentage';
    public float|string $discountValue = 0;
    public bool $showCartDetailModal = false;
    public string|null $removeReason = null;
    public string $transport_name = '';
    public string $transport_address = '';
    public string $transport_distance = '';
    public string $vehicle_number = '';
    public float|string $transport_charge = 0;
    public float $cartTotal = 0;
    public bool $showModsModal = false;
    public ?string $modsKey = null;
    public string $modsItemName = '';
    public ?int $modsVariantId = null;
    public string $modsVariantName = '';
    public array $modsAddons = [];
    public string $cartDiscountType = 'percentage';
    public float|string $cartDiscountValue = 0;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        $this->cartTotal = $this->getCartTotal();
        return view('livewire.waiter.pickup-item', [
            'filteredItems' => $this->getFilteredItems(),
            'cartItems' => $this->cart,
        ]);
    }

    public function mount($id)
    {
        $this->order = Order::findOrFail($id);
        $this->orderId = $id;
        $this->editMode = request()->query('mode') === 'edit';

        $this->items = $this->order->restaurant
            ->items()
            ->where('is_active', 0)
            ->with(['variants', 'discounts'])
            ->get();
        $this->categories = $this->items->pluck('category')->where('is_active', 0)->unique('id')->values();
        $this->orderTypes = collect(OrderType::cases())->mapWithKeys(fn($c) => [$c->value => $c->label()])->toArray();
        $this->paymentMethods = collect(PaymentMethod::cases())->mapWithKeys(fn($case) => [$case->value => $case->label()])->toArray();
        $this->splits = [['method' => '', 'amount' => 0]];

        if ($this->editMode) {
            $this->loadEditModeData($id);
        }

        $this->customerName = $this->order->customer_name ?? '';
        $this->mobile = $this->order->mobile ?? '';
        $this->duoCustomerName = $this->order->customer_name ?? '';
        $this->duoMobile = $this->order->mobile ?? '';
    }

    protected function loadEditModeData($id)
    {
        $order = Order::where('id', $id)->where('status', 'pending')->first();

        $latestKot = Kot::where('order_id', $order->id)->where('status', 'pending')->orderBy('created_at')->get();

        $coustomer = Customer::where('order_id', $order->id)->first();

        if ($coustomer) {
            $this->followupCustomer_name = $coustomer->name;
            $this->followupCustomer_mobile = $coustomer->mobile;
            $this->followupCustomer_email = $coustomer->email ?? '';
            $this->customer_dob = $coustomer->dob ? \Carbon\Carbon::parse($coustomer->dob)->format('Y-m-d') : '';
            $this->customer_anniversary = $coustomer->anniversary ? \Carbon\Carbon::parse($coustomer->anniversary)->format('Y-m-d') : '';
        }

        foreach ($latestKot as $kot) {
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
            $this->transport_name = $order->transport_name ?? '';
            $this->transport_address = $order->transport_address ?? '';
            $this->transport_distance = $order->transport_distance ?? '';
            $this->vehicle_number = $order->vehicle_number ?? '';
            $this->transport_charge = $order->transport_charge ?? 0;
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

        if ($this->cartDiscountType === 'percentage') {
            $discount = ($subtotal * floatval($this->cartDiscountValue)) / 100;
        } elseif ($this->cartDiscountType === 'fixed') {
            $discount = floatval($this->cartDiscountValue);
        }

        $discount = min($discount, $subtotal);
        $service = $this->serviceCharge ?? 0;
        $transport = floatval($this->transport_charge ?? 0);
        return $subtotal - $discount + $service + $transport;
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

        $this->cart[$key] = [
            'id' => $this->cart[$key]['id'] ?? $key,
            'item_id' => $this->cart[$key]['item_id'] ?? null,
            'name' => $this->cart[$key]['name'] ?? '',
            'qty' => 0,
            'price' => 0,
            'addons' => [],
            'note' => '',
            'variant' => $this->cart[$key]['variant'] ?? null,
            'discount' => 0,
        ];
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

        // Only base price is editable, variant & addons excluded
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

            $order = Order::findOrFail($this->orderId);
            $order->update([
                'restaurant_id' => $restaurantId,
                'user_id' => Auth::id(),
                'status' => 'pending',
                'sub_total' => $subTotal,
                'discount_amount' => 0,
                'total_amount' => $subTotal,
                'tax_amount' => 0,
                'service_charge' => $this->serviceCharge,
                'transport_name' => $this->transport_name,
                'transport_address' => $this->transport_address,
                'transport_distance' => !empty($this->transport_distance) ? $this->transport_distance : 0,
                'vehicle_number' => $this->vehicle_number,
                'transport_charge' => $this->transport_charge,
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

        if ($this->editMode) {
            $this->updateOrder();
            session()->flash('success', 'KOT updated!');
            return redirect()->route('restaurant.pickup.create')->with('success', 'KOT updated!');
        }

        $this->createOrderAndKot();
        $this->reset(['cart', 'showVariantModal']);
        return redirect()->route('restaurant.pickup.create')->with('success', 'Order placed!');
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

            return redirect()->route('restaurant.waiter.dashboard')->with('success', 'KOT updated & printed!');
        }

        $this->createOrderAndKot(true);
        $this->reset(['cart', 'showVariantModal', 'noteInput', 'currentNoteKey']);

        return redirect()->route('restaurant.pickup.create')->with('success', 'Order placed & KOT printed!');
    }

    public function updateOrder()
    {
        if (empty($this->cart)) {
            $this->addError('cart', 'Cart is empty!');
            return;
        }

        DB::transaction(function () {
            $order = Order::where('id', $this->orderId)->first();
            $subTotal = $this->getCartTotal();

            $order->update([
                'status' => 'pending',
                'sub_total' => $subTotal,
                'discount_amount' => 0,
                'total_amount' => $subTotal,
                'tax_amount' => 0,
                'service_charge' => $this->serviceCharge,
                'transport_name' => $this->transport_name,
                'transport_address' => $this->transport_address,
                'transport_distance' => $this->transport_distance,
                'vehicle_number' => $this->vehicle_number,
                'transport_charge' => $this->transport_charge,
            ]);

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
        return redirect()->route('restaurant.pickup.create')->with('success', 'Order Payment Complete!');
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
            $this->createOrderAndKot();
            $order = Order::where('id', $this->order)->where('status', 'pending')->latest()->first();
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

            $order->update(['status' => 'served']);
            $orderItems->each(fn($item) => $item->update(['status' => 'served']));
            $amount = $this->getCartTotal();

            Payment::create([
                'restaurant_id' => $order->restaurant_id,
                'order_id' => $order->id,
                'amount' => $amount,
                'method' => $this->paymentMethod,
            ]);
            $this->totalSale($order->restaurant_id, $order->total_amount);
        }

        return redirect()->route('restaurant.pickup.create')->with('success', 'Order Payment Complete!');
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

        $order = Order::where('id', $this->order)->where('status', 'pending')->latest()->first();

        if (!$order) {
            $this->createOrderAndKot();
            $order = Order::where('id', $this->order)->where('status', 'pending')->latest()->first();
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

            $order->update(['status' => 'served']);
            $orderItems->each(fn($item) => $item->update(['status' => 'served']));
            $amount = $this->getCartTotal();

            Payment::create([
                'restaurant_id' => $order->restaurant_id,
                'order_id' => $order->id,
                'amount' => $amount,
                'method' => $this->paymentMethod,
            ]);
            $this->totalSale($order->restaurant_id, $order->total_amount);
        }

        // $this->dispatch('printBill', billId: $order->id);
        $this->dispatch('btPrintBill', orderId: $order->id);
        return redirect()->route('restaurant.pickup.create')->with('success', 'Order Payment Complete!');
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

        $total = collect($this->splits)->sum('amount');
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
            $order->update([
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

        return redirect()->route('restaurant.pickup.create')->with('success', 'Order split payment recorded!');
    }

    public function confirmDuoPayment()
    {
        $this->validate([
            'duoCustomerName' => 'required|string|max:100',
            'duoMobile' => 'required|string|max:20',
            'duoAmount' => 'required|numeric|min:0.01',
            'duoMethod' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    if (!is_null($value) && !PaymentMethod::tryFrom($value)) {
                        $fail('The selected payment method is invalid.');
                    }
                },
            ],
            'duoIssue' => 'nullable|string|max:255',
        ]);

        if (auth()->user()->restaurant_id) {
            $restaurantId = auth()->user()->restaurant_id;
        } else {
            $restaurantId = Restaurant::where('user_id', auth()->id())->value('id');
        }
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
            'restaurant_id' => $restaurantId,
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

        return redirect()->route('restaurant.pickup.create')->with('success', 'Duo Payment Completed!');
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

            $order = Order::where('id', $this->order->id)->where('status', 'pending')->latest()->firstOrFail();
            $kot = Kot::where('order_id', $order->id)->where('status', 'pending')->latest()->first();

            OrderItem::where('order_id', $order->id)
                ->where('item_id', $itemId)
                ->when($variantId, fn($q) => $q->where('variant_id', $variantId))
                ->update([
                    'delete_reason' => $reason,
                    'deleted_at' => now(),
                ]);

            if ($kot) {
                KOTItem::where('kot_id', $kot->id)->where('item_id', $itemId)->when($variantId, fn($q) => $q->where('variant_id', $variantId))->delete();
            }
        });

        unset($this->cart[$key]);
        $this->showRemoveModal = false;
        $this->reset(['showRemoveModal', 'removeKey', 'removeReason']);
    }

    public function openCustomerModal()
    {
        $this->resetValidation();
        $order = Order::find($this->orderId);
        if (!$order) {
            session()->flash('error', 'Order not found.');
            return;
        }
        if ($order->customer_id) {
            $customer = Customer::find($order->customer_id);
            if ($customer) {
                $this->followupCustomer_name = $customer->name ?? '';
                $this->followupCustomer_mobile = $customer->mobile ?? '';
                $this->followupCustomer_email = $customer->email ?? '';
                $this->customer_dob = $customer->dob ? \Carbon\Carbon::parse($customer->dob)->format('Y-m-d') : '';
                $this->customer_anniversary = $customer->anniversary ? \Carbon\Carbon::parse($customer->anniversary)->format('Y-m-d') : '';
            }
        } else {
            $this->followupCustomer_name = '';
            $this->followupCustomer_mobile = '';
            $this->followupCustomer_email = '';
            $this->customer_dob = '';
            $this->customer_anniversary = '';
        }

        $this->showCustomerModal = true;
    }

    public function saveCustomer()
    {
        if (auth()->user()->restaurant_id) {
            $restaurantId = auth()->user()->restaurant_id;
        } else {
            $restaurantId = Restaurant::where('user_id', auth()->id())->value('id');
        }

        $this->validate([
            'followupCustomer_name' => 'required|string|max:100',
            'followupCustomer_mobile' => 'required|string|max:20',
            'followupCustomer_email' => 'nullable|email',
            'customer_dob' => 'nullable|date',
            'customer_anniversary' => 'nullable|date',
        ]);

        $order = Order::where('id', $this->order)->where('status', 'pending')->latest()->firstOrFail();
        $customer = Customer::where('order_id', $order->id)->first();

        if (!$customer) {
            $customer = Customer::create([
                'order_id' => $order->id,
                'name' => $this->followupCustomer_name,
                'mobile' => $this->followupCustomer_mobile,
                'email' => $this->followupCustomer_email,
                'dob' => $this->customer_dob,
                'anniversary' => $this->customer_anniversary,
                'restaurant_id' => $restaurantId,
            ]);
        } else {
            $customer->update([
                'name' => $this->followupCustomer_name,
                'mobile' => $this->followupCustomer_mobile,
                'email' => $this->followupCustomer_email,
                'dob' => $this->customer_dob,
                'anniversary' => $this->customer_anniversary,
            ]);
        }

        $order->update([
            'customer_id' => $customer->id,
        ]);

        $this->showCustomerModal = false;

        session()->flash('success', 'Customer added and linked to order!');
    }

    public function openModsModal(string $key)
    {
        if (!isset($this->cart[$key])) {
            return;
        }

        $this->modsKey = $key;
        $row = $this->cart[$key];

        $this->modsItemName = $row['name'] ?? '';

        $this->modsVariantId = null;
        $this->modsVariantName = '';
        if (str_starts_with($key, 'v')) {
            $this->modsVariantId = (int) substr($key, 1);
        } elseif (!empty($row['variant_price'] ?? 0)) {
            $this->modsVariantId = $row['variant_id'] ?? null;
        }

        if ($this->modsVariantId) {
            $variant = Variant::find($this->modsVariantId);
            $this->modsVariantName = $variant?->name ?? 'Variant';
        }

        $ids = $row['addons'] ?? [];
        if (!is_array($ids)) {
            $ids = [];
        }
        $addons = [];
        if (count($ids)) {
            $addons = Addon::whereIn('id', $ids)
                ->get(['id', 'name', 'price'])
                ->map(fn($a) => ['id' => $a->id, 'name' => $a->name, 'price' => $a->price])
                ->toArray();
        }
        $this->modsAddons = $addons;
        $this->showModsModal = true;
    }

    private function recalcRowTotals(string $key)
    {
        if (!isset($this->cart[$key])) {
            return;
        }

        $row = &$this->cart[$key];
        $base = (float) ($row['base_price'] ?? ($row['price'] ?? 0));
        $variant = (float) ($row['variant_price'] ?? 0);
        $addons = (float) ($row['addons_price'] ?? 0);

        if (!empty($row['discount_type'])) {
            $dval = (float) ($row['discount_value'] ?? 0);
            if ($row['discount_type'] === 'percentage') {
                $base = max($base - ($base * $dval) / 100, 0);
            } elseif ($row['discount_type'] === 'fixed') {
                $base = max($base - $dval, 0);
            }
            $row['base_price'] = $base;
        }

        $row['price'] = round($base + $variant + $addons, 2);
    }

    public function removeVariant()
    {
        if (!$this->modsKey || !isset($this->cart[$this->modsKey])) {
            return;
        }

        $oldKey = $this->modsKey;
        $row = $this->cart[$oldKey];

        $hasVariant = str_starts_with($oldKey, 'v') || !empty($row['variant_price'] ?? 0);
        if (!$hasVariant) {
            return;
        }

        $baseItemId = $row['item_id'] ?? null;
        if (!$baseItemId) {
            $baseItemId = (int) preg_replace('/-new\d*/', '', $row['id'] ?? $oldKey);
        }

        $newKey = (string) $baseItemId;
        if (isset($this->cart[$newKey])) {
            $suffix = 1;
            while (isset($this->cart[$newKey . "-new{$suffix}"])) {
                $suffix++;
            }
            $newKey = $newKey . "-new{$suffix}";
        }

        $newRow = $row;
        $newRow['id'] = $newKey;
        $newRow['variant_price'] = 0;
        $newRow['variant_id'] = null;
        $newRow['name'] = preg_replace('/\s*\([^)]*\)\s*$/', '', $row['name'] ?? '');
        $newRow['price'] = (float) ($row['base_price'] ?? 0) + (float) ($row['addons_price'] ?? 0);

        $this->cart[$newKey] = $newRow;
        unset($this->cart[$oldKey]);

        $this->recalcRowTotals($newKey);
        $this->modsKey = $newKey;
        $this->modsVariantId = null;
        $this->modsVariantName = '';
    }

    public function removeAddon(int $addonId)
    {
        if (!$this->modsKey || !isset($this->cart[$this->modsKey])) {
            return;
        }

        $row = &$this->cart[$this->modsKey];
        $row['addons'] = array_values(array_filter($row['addons'] ?? [], fn($id) => (int) $id !== (int) $addonId));

        $addon = Addon::find($addonId);
        if ($addon) {
            $row['addons_price'] = max(0, (float) ($row['addons_price'] ?? 0) - (float) $addon->price);
        }

        $this->recalcRowTotals($this->modsKey);
        $this->modsAddons = array_values(array_filter($this->modsAddons, fn($a) => (int) $a['id'] !== (int) $addonId));
    }
}
