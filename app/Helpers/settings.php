<?php

use App\Models\AppConfiguration;
use App\Models\RestaurantConfiguration;

function setting($key, $default = null)
{
    $user = auth()->user();
    if (!$user) {
        // logger("No authenticated user.");
        return $default;
    }

    $restaurant = $user->restaurants()->first();
    if (!$restaurant) {
        // logger("No restaurant found for user {$user->id}");
        return $default;
    }

    $restaurantId = $restaurant->id;

    $configId = AppConfiguration::where('key', $key)->value('id');
    if (!$configId) {
        // logger("No AppConfiguration found for key '$key'");
        return $default;
    }

    $value = RestaurantConfiguration::where('restaurant_id', $restaurantId)
        ->where('configuration_id', $configId)
        ->value('value');

    // logger("setting('$key') resolved to: $value");

    return $value ?? $default;
}
