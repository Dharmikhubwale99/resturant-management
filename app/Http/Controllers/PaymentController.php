<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use App\Models\{Plan, Restaurant};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\{PlanFeature, AppConfiguration, RestaurantConfiguration};

class PaymentController extends Controller
{
    /**
     * Create Razorpay Order with discount support
     */
    public function createRazorpayOrder(Plan $plan)
    {
        $api_key = config('razorpay.api_key');
        $api_secret = config('razorpay.api_secret');
        $api = new Api($api_key, $api_secret);

        $price = $plan->price;
        if ($plan->type === 'fixed' && $plan->amount) {
            $price -= $plan->amount;
        } elseif ($plan->type === 'percentage' && $plan->value) {
            $price -= ($plan->price * $plan->value / 100);
        }
        $price = max(0, $price);

        $razorpayOrder = $api->order->create([
            'receipt' => 'order_rcptid_' . uniqid(),
            'amount' => $price * 100,
            'currency' => 'INR',
        ]);

        session([
            'razorpay_order_id' => $razorpayOrder['id'],
            'plan_id' => $plan->id,
        ]);

        return response()->json([
            'api_key' => $api_key,
            'order_id' => $razorpayOrder['id'],
            'amount' => $razorpayOrder['amount'],
            'callback_url' => route('razorpay.callback'),
            'plan_name' => $plan->name,
        ]);
    }

    public function handleCallback(Request $request)
    {
        $api_key = config('razorpay.api_key');
        $api_secret = config('razorpay.api_secret');
        $api = new Api($api_key, $api_secret);

        $expectedOrderId = session('razorpay_order_id');

        try {
            $api->utility->verifyPaymentSignature([
                'razorpay_order_id' => $expectedOrderId,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature' => $request->razorpay_signature,
            ]);

            $user = Auth::user();
            $restaurant = $user->restaurants()->first();
            $plan = Plan::find(session('plan_id'));

            $restaurant = Restaurant::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'plan_id' => $plan->id,
                    'plan_expiry_at' => Carbon::now()->addDays($plan->duration_days),
                ]
            );

            $this->syncRestaurantFeatures($restaurant, $plan);

            session()->forget(['razorpay_order_id', 'plan_id']);

            if (empty($restaurant->name) || empty($restaurant->email) || empty($restaurant->mobile) || empty($restaurant->address) || empty($restaurant->pin_code_id)) {
                return redirect()->route('restaurant.resto-register')->with('info', 'Please complete your restaurant profile.');
            } else {
                return redirect()->route('restaurant.dashboard')->with('success', 'Payment successful!');
            }

        } catch (\Exception $e) {
            Log::error('Razorpay callback error: ' . $e->getMessage());
            return redirect()->route('plan.purchase')->with('error', 'Payment failed or signature mismatch.');
        }
    }

    public function activateFreePlan(Plan $plan)
    {
        if ($plan->price > 0) {
            return response()->json(['success' => false], 403);
        }

        $user = Auth::user();
        $restaurant = $user->restaurants()->firstOrCreate(
            ['user_id' => $user->id],
            [
                'plan_id' => $plan->id,
                'plan_expiry_at' => Carbon::now()->addDays($plan->duration_days),
            ]
        );

        $restaurant->update([
            'plan_id' => $plan->id,
            'plan_expiry_at' => Carbon::now()->addDays($plan->duration_days),
        ]);
        $this->syncRestaurantFeatures($restaurant, $plan);
        session()->forget(['razorpay_order_id', 'plan_id']);

        return response()->json([
            'success' => true,
            'redirect' => route('restaurant.dashboard'),
        ]);
    }

    protected function syncRestaurantFeatures($restaurant, $plan)
    {
        Log::info('Syncing restaurant features for plan: ' . $plan->name);
        Log::info('Restaurant ID: ' . $restaurant->id);
        RestaurantConfiguration::where('restaurant_id', $restaurant->id)->delete();

        foreach ($plan->planFeatures as $feature) {
            $configId = AppConfiguration::where('key', $feature->feature)->value('id');

            if ($configId) {
                RestaurantConfiguration::create([
                    'restaurant_id' => $restaurant->id,
                    'configuration_id' => $configId,
                    'value' => $feature->is_active ? 1 : 0,
                ]);
            }
        }
    }
}
