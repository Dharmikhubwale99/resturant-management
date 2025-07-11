<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class KOTItem extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'kot_id',
        'item_id',
        'variant_id',
        'quantity',
        'status',
        'special_notes',
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
            'kot_id' => 'integer',
            'item_id' => 'integer',
            'variant_id' => 'integer',
        ];
    }

    public function kot(): BelongsTo
    {
        return $this->belongsTo(Kot::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class);
    }

    public function addons()
    {
        return $this->belongsToMany(Addon::class, 'kot_item_addons', 'kot_item_id', 'addon_id')
                    ->withPivot('price')
                    ->withTimestamps();
    }

}
