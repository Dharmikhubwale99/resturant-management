<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestaurantConfiguration extends Model
{
    protected $fillable = ['restaurant_id', 'configuration_id', 'value'];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function configuration()
    {
        return $this->belongsTo(AppConfiguration::class, 'configuration_id');
    }
}
