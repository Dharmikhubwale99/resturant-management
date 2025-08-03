<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MoneyOut extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'party_name',
        'amount',
        'description',
        'date',
        'restaurant_id',
    ];
}
