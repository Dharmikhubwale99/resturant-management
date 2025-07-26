<?php

use App\Models\AppConfiguration;
use App\Models\RestaurantConfiguration;
use Illuminate\Support\Facades\Log;

function setting($key, $default = null)
{
    $user = auth()->user();

    if (!$user) {
        logger("No authenticated user.");
        return $default;
    }

    $restaurantId = $user->restaurant_id;
    // Log::info("User {$user->id} has restaurant_id: $restaurantId");

    if (!$restaurantId && method_exists($user, 'restaurants')) {
        $restaurant = $user->restaurants()->first();
        $restaurantId = $restaurant?->id;
        // Log::info("User {$user->id} has multiple restaurants, using first one: $restaurantId");
    }

    if (!$restaurantId) {
        logger("No restaurant found for user {$user->id}");
        return $default;
    }

    $configId = AppConfiguration::where('key', $key)->value('id');
    if (!$configId) {
        logger("No AppConfiguration found for key '$key'");
        return $default;
    }

    $value = RestaurantConfiguration::where('restaurant_id', $restaurantId)
        ->where('configuration_id', $configId)
        ->value('value');

    // logger("setting('$key') resolved to: $value");

    return $value ?? $default;
}
