<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SalesSummaries extends Model
{
    protected $table = 'sales_summaries';

    protected $fillable = [
        'restaurant_id',
        'summary_date',
        'total_sale',
        'total_expances'
    ];

    public function restaurant()
    {
        return $this->belongsTo(Restaurant::class);
    }
}
