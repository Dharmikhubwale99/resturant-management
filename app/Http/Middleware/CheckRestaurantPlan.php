<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRestaurantPlan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   // app/Http/Middleware/CheckRestaurantPlan.php
    public function handle($request, Closure $next)
    {
        $user = Auth::user();
        $restaurant = $user->restaurants()->first();

        if (!$restaurant || !$restaurant->plan_id || now()->greaterThan($restaurant->plan_expiry_at)) {
            return redirect()->route('plan.purchase')->with('error', 'Your restaurant plan has expired or is not set. Please contact support.');
        }

        return $next($request);
    }

}
