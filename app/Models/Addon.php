<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Addon extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'item_id',
        'name',
        'price',
    ];

    protected function casts(): array
    {
        return [
            'id' => 'integer',
            'item_id' => 'integer',
        ];
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
