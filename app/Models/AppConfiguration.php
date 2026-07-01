<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppConfiguration extends Model
{
    protected $table = 'app_configurations';
    protected $fillable = ['key', 'value'];
    public $timestamps = true;

    public function restaurants()
    {
        return $this->hasMany(RestaurantConfiguration::class, 'configuration_id');
    }


}
