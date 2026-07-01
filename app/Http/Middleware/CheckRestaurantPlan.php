<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;
use App\Models\Restaurant;

class CheckRestaurantPlan
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
   public function handle($request, Closure $next)
   {
       $user = Auth::user();

       if ($user->restaurant_id) {
           $restaurantId = $user->restaurant_id;
       } else {
           $restaurantId = Restaurant::where('user_id', $user->id)->value('id');
       }

       $restaurant = Restaurant::find($restaurantId);

       if (!$restaurant || !$restaurant->plan_id || now()->greaterThan($restaurant->plan_expiry_at)) {
           return redirect()->route('plan.purchase')->with('error', 'Your restaurant plan has expired or is not set. Please contact support.');
       }

       return $next($request);
   }

}
