<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_number',
        'restaurant_id',
        'table_id',
        'user_id',
        'customer_id',
        'discount_id',
        'order_type',
        'status',
        'sub_total',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'notes',
        'customer_name',
        'mobile',
        'service_charge',
        'transport_name',
        'transport_address',
        'transport_distance',
        'vehicle_number',
        'transport_charge',
        'bill_number'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'restaurant_id' => 'integer',
            'table_id' => 'integer',
            'user_id' => 'integer',
            'customer_id' => 'integer',
            'discount_id' => 'integer',
            'sub_total' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function visits(): HasMany
    {
        return $this->hasMany(Visit::class);
    }

    public function kots(): HasMany
    {
        return $this->hasMany(Kot::class);
    }

    protected static function booted()
    {
        static::creating(function ($order) {
            if (empty($order->order_number)) {
                do {
                    $orderNumber = 'ORD-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6));
                } while (self::where('order_number', $orderNumber)->exists());

                $order->order_number = $orderNumber;
            }
        });
    }

    public static function generateBillNumber($restaurantId): string
    {
        do {
            $billNumber = 'BILL-' . Str::upper(Str::random(6));
        } while (self::where('bill_number', $billNumber)->where('restaurant_id', $restaurantId)->exists());

        return $billNumber;
    }

    public function items()
    {
        return $this->hasMany(\App\Models\OrderItem::class);
    }

    public function paymentLogs()
    {
        return $this->hasMany(\App\Models\RestaurantPaymentLog::class, 'order_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function paymentGroups()
    {
        return $this->hasMany(\App\Models\PaymentGroup::class, 'order_id');
    }
}
