<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Razorpay\Api\Api;
use App\Models\{Plan, Restaurant};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Models\{PlanFeature, AppConfiguration, RestaurantConfiguration};
use App\Traits\HasRolesAndPermissions;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    use HasRolesAndPermissions;
    /**
     * Create Razorpay Order with discount support
     */
    public function createRazorpayOrder(Plan $plan)
    {
        $api_key    = config('razorpay.api_key');
        $api_secret = config('razorpay.api_secret');
    
        $price = $plan->price;
        if ($plan->type === 'fixed' && $plan->amount) {
            $price -= $plan->amount;
        } elseif ($plan->type === 'percentage' && $plan->value) {
            $price -= ($plan->price * $plan->value) / 100;
        }
        $price = max(0, $price);
        $amountPaise = (int) round($price * 100);
    
        $payload = [
            'amount'   => $amountPaise,
            'currency' => 'INR',
            'receipt'  => 'order_rcptid_' . uniqid(),
            'notes'    => ['plan_id' => (string) $plan->id],
        ];
    
        $response = Http::withOptions([
            'force_ip_resolve' => 'v4',
            'verify' => true,
            'curl' => [
                CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
            ],
        ])->withBasicAuth($api_key, $api_secret)
          ->post('https://api.razorpay.com/v1/orders', $payload);
    
        if (!$response->successful()) {
            Log::error('Razorpay order creation failed', [
                'status' => $response->status(),
                'body'   => $response->json() ?: $response->body(),
            ]);
            return back()->with('error', $response->json('error.description') ?? 'Unable to create Razorpay order.');
        }
    
        $razorpayOrder = $response->json();
    
        session([
            'razorpay_order_id'    => $razorpayOrder['id'],
            'plan_id'              => $plan->id,
            'order_amount_paise'   => $amountPaise,    
            'order_currency'       => 'INR',
        ]);
    
        return response()->json([
            'api_key'      => $api_key,
            'order_id'     => $razorpayOrder['id'],
            'amount'       => $razorpayOrder['amount'],
            'callback_url' => route('razorpay.callback'),
            'plan_name'    => $plan->name,
        ]);
    }


   public function handleCallback(Request $request)
    {
        // Log::info('Validate' . $request);
        $api_key = config('razorpay.api_key');
        $api_secret = config('razorpay.api_secret');
        $api = new Api($api_key, $api_secret);

        $attrs = $request->only('razorpay_payment_id', 'razorpay_order_id', 'razorpay_signature');

        try {
            $api->utility->verifyPaymentSignature($attrs);

            if (($attrs['razorpay_order_id'] ?? null) !== session('razorpay_order_id')) {
                return back()->with('error', 'Order mismatch or session expired.');
            }

            $p =  Http::withOptions([
                'force_ip_resolve' => 'v4',
                'verify' => true,
                'curl' => [
                    CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                    CURLOPT_SSLVERSION => CURL_SSLVERSION_TLSv1_2,
                ],
            ])
                ->withBasicAuth('rzp_live_QQgS1hYiv7HXqw', 'i3fTHpVfg2jlVUxCREoX13cx')
                ->get("https://api.razorpay.com/v1/payments/{$attrs['razorpay_payment_id']}");


            $status   = $p['status'] ?? null;
            // Log::info('Payment status', [
            //     'status' => $status,
            // ]);

            if ($status === 'captured') {
                // Log::warning('Payment success', [
                //     'status' => $status,
                //     'payment_id' => $attrs['razorpay_payment_id'],
                // ]);

                $user = Auth::user();
                $restaurant = $user->restaurants()->first();
                // Log::info('Restaurant found', [
                //     'restaurant_id' => $restaurant->id ?? null,
                // ]);
                $plan = Plan::find(session('plan_id'));

                DB::transaction(function () use ($user, $plan, &$restaurant) {
                    $restaurant = Restaurant::updateOrCreate(
                        ['user_id' => $user->id],
                        [
                            'plan_id'        => $plan->id,
                            'plan_expiry_at' => now()->addDays($plan->duration_days),
                        ]
                    );
                });

                $permissions = $this->getAllPermissions();
                foreach ($permissions as $perm) {
                    Permission::firstOrCreate(['name' => $perm]);
                }
                $user->givePermissionTo($permissions);

                $this->syncRestaurantFeatures($restaurant, $plan);

                session()->forget(['razorpay_order_id', 'plan_id']);

                if (empty($restaurant->name) || empty($restaurant->email) || empty($restaurant->mobile) || empty($restaurant->address) || empty($restaurant->pin_code_id)) {
                    return redirect()->route('restaurant.resto-register')->with('info', 'Please complete your restaurant profile.');
                } else {
                    return redirect()->route('restaurant.dashboard')->with('success', 'Payment successful!');
                }
            }
        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            // Log::error('Signature mismatch: '.$e->getMessage());
            return redirect()->route('plan.purchase')->with('error', 'Signature mismatch.');
        } catch (\Exception $e) {
            // Log::error('Callback error: '.$e->getMessage());
            return redirect()->route('plan.purchase')->with('error', 'Unable to verify payment.');
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
            ],
        );

        $restaurant->update([
            'plan_id' => $plan->id,
            'plan_expiry_at' => Carbon::now()->addDays($plan->duration_days),
        ]);
        $permissions = $this->getAllPermissions(); 
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }
        $user->givePermissionTo($permissions);

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
