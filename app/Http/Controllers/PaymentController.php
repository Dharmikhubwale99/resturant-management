<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use App\Models\{Plan, Restaurant};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PaymentController extends Controller
{
    /**
     * Create Razorpay Order (AJAX triggered)
     */
    public function createRazorpayOrder(Plan $plan)
    {
        $api_key = config('razorpay.api_key');
        $api_secret = config('razorpay.api_secret');
        $api = new Api($api_key, $api_secret);
        Log::info("message", ['plan' => $plan]);

        $razorpayOrder = $api->order->create([
            'receipt' => 'order_rcptid_' . uniqid(),
            'amount' => $plan->price * 100,
            'currency' => 'INR'
        ]);
        Log::info("message", ['razorpayOrder' => $razorpayOrder]);

        session([
            'razorpay_order_id' => $razorpayOrder['id'],
            'plan_id' => $plan->id,
        ]);
        return response()->json([
            'api_key' => config('services.razorpay.key'),
            'order_id' => $razorpayOrder['id'],
            'amount' => $razorpayOrder['amount'],
            'callback_url' => route('razorpay.callback'),
            'plan_name' => $plan->name
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
                'razorpay_order_id'   => $expectedOrderId,
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_signature'  => $request->razorpay_signature,
            ]);

            Log::info("message Attribubte verified successfully");
            $user     = Auth::user();
            $restaurant = Auth::user()->restaurants()->first();
            $plan = Plan::find(session('plan_id'));
            Log::info("message", ['restaurant' => $restaurant]);

            $restaurant = Restaurant::firstOrCreate(
                ['user_id' => $user->id],
                [
                    'plan_id' => $plan->id,
                    'plan_expiry_at' => Carbon::now()->addDays($plan->duration_days),
                ]
            );

            return redirect()->route('resturant.dashboard')->with('success', 'Payment successful!');
        } catch (\Exception $e) {
            return redirect()->route('plan.purchase')->with('error', 'Payment failed or signature mismatch.');
        }
    }
}
