<?php

namespace App\Traits;
use App\Models\SalesSummaries;

trait TransactionTrait
{
    protected function totalSale($restaurantId = null, $amount)
    {
        $today = now()->format('Y-m-d');

        $sale = SalesSummaries::where('restaurant_id', $restaurantId)->latest('summary_date')->first();

        if (!$sale || $sale->summary_date !== $today) {
            SalesSummaries::create([
                'restaurant_id' => $restaurantId,
                'total_sale' => $amount,
                'summary_date' => $today,
            ]);
        } else {
            $sale->update([
                'total_sale' => $sale->total_sale + $amount,
            ]);
        }
    }
}
