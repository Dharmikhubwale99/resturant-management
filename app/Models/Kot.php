<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Kot extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'order_id',
        'table_id',
        'kot_number',
        'status',
        'printed_at',
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
            'order_id' => 'integer',
            'table_id' => 'integer',
            'printed_at' => 'timestamp',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function table(): BelongsTo
    {
        return $this->belongsTo(Table::class);
    }

    public function kOTItems(): HasMany
    {
        return $this->hasMany(KOTItem::class);
    }

    protected static function booted()
    {
        static::creating(function ($kot) {
            if (empty($kot->kot_number)) {
                $kot->kot_number = 'KOT-' . now()->format('ymd') . Str::upper(Str::random(4));
            }
        });
    }

}
