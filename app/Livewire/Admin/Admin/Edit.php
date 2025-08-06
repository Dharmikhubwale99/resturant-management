<?php

namespace App\Livewire\Admin\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;
use App\Models\{User, Restaurant, Country, State, City, District, PinCode, Setting, Plan, AppConfiguration, RestaurantConfiguration};
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use App\Traits\HasRolesAndPermissions;

class Edit extends Component
{
    use WithFileUploads, HasRolesAndPermissions;

    public $user_id, $restaurant_id, $setting_id;
    public $user_name, $email, $mobile, $password, $password_confirmation;
    public $pincode, $pincode_id, $country_name, $state_name, $city_name, $district_name;
    public $country_id, $state_id, $city_id, $district_id;
    public $restaurant_name, $restaurant_address, $gst_no;
    public $meta_title, $meta_description, $meta_keywords, $favicon, $oldFavicon;
    public $plan_id;
    public $plans = [];

    #[Layout('components.layouts.admin.app')]
    public function render()
    {
        return view('livewire.admin.admin.edit');
    }
    public function mount($id)
    {
        $user = User::with('restaurants', 'setting')->findOrFail($id);
        $restaurant = $user->restaurants->first();
        $setting = $user->setting;
        $this->user_id = $user->id;
        $this->restaurant_id = $restaurant->id ?? null;
        $this->setting_id = $setting?->id;

        $this->user_name = $user->name;
        $this->email = $user->email;
        $this->mobile = $user->mobile;

        $this->pincode_id = $user->pin_code_id;
        $pincode = PinCode::with('district.city.state.country')->find($this->pincode_id);

        if ($pincode) {
            $this->pincode = $pincode->code;
            $this->setLocationFromModels($pincode->district->city->state->country, $pincode->district->city->state, $pincode->district->city, $pincode->district);
        }

        if ($restaurant) {
            $this->restaurant_name = $restaurant->name;
            $this->restaurant_address = $restaurant->address;
            $this->gst_no = $restaurant->gstin;
            $this->plan_id = $restaurant->plan_id;
        }

        if ($setting) {
            $this->meta_title = $setting->meta_title;
            $this->meta_description = $setting->meta_description;
            $this->meta_keywords = $setting->meta_keywords;
            $this->favicon = $setting->favicon;
            $this->oldFavicon = $setting->favicon;
        }

        $this->plans = Plan::where('is_active', 0)->get()->mapWithKeys(function ($plan) {
            return [
                  $plan->id => $plan->name . ' | ₹' . number_format($plan->price, 2) . ' | ' . $plan->duration_days . ' days',
            ];
        });
    }

    public function updatedPincode($value)
    {
        $cached = PinCode::with('district.city.state.country')->where('code', $value)->first();

        if ($cached) {
            $this->pincode_id = $cached->id;
            $this->setLocationFromModels($cached->district->city->state->country, $cached->district->city->state, $cached->district->city, $cached->district);
            return;
        }

        try {
            $response = Http::retry(3, 200)
                ->timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Linux; Android 10) AppleWebKit/537.36 Chrome/114.0.0.0 Safari/537.36',
                ])
                ->withOptions([
                    'curl' => [
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_FORBID_REUSE => true,
                        CURLOPT_FRESH_CONNECT => true,
                        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                    ],
                ])
                ->get("https://api.postalpincode.in/pincode/{$value}");

            if ($response->successful()) {
                $data = $response->json()[0];

                if ($data['Status'] === 'Success' && count($data['PostOffice']) > 0) {
                    $post = $data['PostOffice'][0];

                    $country = Country::firstOrCreate(['name' => $post['Country']]);
                    $state = State::firstOrCreate(['name' => $post['State'], 'country_id' => $country->id]);
                    $city = City::firstOrCreate(['name' => $post['Block'] ?? $post['District'], 'state_id' => $state->id]);
                    $district = District::firstOrCreate(['name' => $post['District'], 'city_id' => $city->id]);

                    $pincode = PinCode::create([
                        'code' => $value,
                        'district_id' => $district->id,
                    ]);

                    $this->pincode_id = $pincode->id;
                    $this->setLocationFromModels($country, $state, $city, $district);
                } else {
                    session()->flash('message', 'Invalid pincode or no result found.');
                }
            } else {
                session()->flash('message', 'API request failed. Status: ' . $response->status());
            }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            logger()->error('Pincode API error: ' . $e->getMessage());
            session()->flash('message', 'Pincode service temporarily unavailable. Please try again later.');
        }
    }

    protected function setLocationFromModels($country, $state, $city, $district)
    {
        $this->country_id = $country->id;
        $this->state_id = $state->id;
        $this->city_id = $city->id;
        $this->district_id = $district->id;

        $this->country_name = $country->name;
        $this->state_name = $state->name;
        $this->city_name = $city->name;
        $this->district_name = $district->name;
    }

    public function update()
    {
        $this->validate([
            'user_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $this->user_id,
            'mobile' => ['required', 'regex:/^[0-9]{10}$/'],
            'pincode' => 'required|digits:6',
            'restaurant_name' => 'nullable|string|max:255',
            'restaurant_address' => 'nullable|string',
            'gst_no' => 'nullable|string|max:15',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'favicon' => 'nullable',
            'plan_id' => 'nullable|exists:plans,id',
        ]);

        $user = User::findOrFail($this->user_id);
        if ($this->password) {
            $this->validate(['password' => 'required|min:6']);
            $hashedPassword = Hash::make($this->password);
        } else {
            $hashedPassword = $user->password;
        }

        if ($this->plan_id) {
            $selectedPlan = Plan::find($this->plan_id);
            $planId = $selectedPlan->id;
            $expiryDate = now()->addDays($selectedPlan->duration_days ?? 30);
        } else {
            $selectedPlan = null;
            $planId = null;
            $expiryDate = null;
        }

        $user->update([
            'name' => $this->user_name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'pin_code_id' => $this->pincode_id,
            'password' => $hashedPassword,
        ]);

        if ($this->restaurant_id) {
            Restaurant::where('id', $this->restaurant_id)->update([
                'name' => $this->restaurant_name,
                'address' => $this->restaurant_address,
                'gstin' => $this->gst_no,
                'pin_code_id' => $this->pincode_id,
                'plan_id' => $planId,
                'plan_expiry_at' => $expiryDate,
            ]);
            $restaurant = Restaurant::find($this->restaurant_id);
        } else {
            $restaurant = Restaurant::create([
                'user_id' => $user->id,
                'name' => $this->restaurant_name,
                'address' => $this->restaurant_address,
                'gstin' => $this->gst_no,
                'pin_code_id' => $this->pincode_id,
                'plan_id' => $planId,
                'plan_expiry_at' => $expiryDate,
            ]);
            $this->restaurant_id = $restaurant->id;
        }

        $faviconPath = $this->oldFavicon;
        if ($this->favicon && $this->favicon !== $this->oldFavicon) {
            $faviconPath = $this->favicon->store('icon', 'public');
        } elseif ($this->favicon === null) {
            $faviconPath = null;
        }

        if ($this->setting_id) {
            Setting::where('id', $this->setting_id)->update([
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'meta_keywords' => $this->meta_keywords,
                'favicon' => $faviconPath,
            ]);
        } else {
            Setting::create([
                'user_id' => $user->id,
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'meta_keywords' => $this->meta_keywords,
                'favicon' => $faviconPath,
            ]);
        }

        if ($this->plan_id) {
            $this->syncRestaurantFeatures($restaurant, $selectedPlan);
        }

        $permissions = $this->getAllPermissions();
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }
        $user->givePermissionTo($permissions);

        session()->flash('success', 'User & Restaurant updated successfully.');
        return redirect()->route('superadmin.admin.index');
    }

    protected function syncRestaurantFeatures($restaurant, $plan)
    {
        Log::info('Syncing restaurant features for plan: ' . $plan->name);
        Log::info('Restaurant ID: ' . $restaurant->id);

        $configMap = AppConfiguration::pluck('id', 'key')->toArray();

        foreach ($plan->planFeatures as $feature) {
            $configId = $configMap[$feature->feature] ?? null;

            if ($configId) {
                RestaurantConfiguration::updateOrCreate(
                    [
                        'restaurant_id' => $restaurant->id,
                        'configuration_id' => $configId,
                    ],
                    [
                        'value' => $feature->is_active ? 1 : 0,
                    ],
                );
            } else {
                Log::warning('No AppConfiguration found for feature: ' . $feature->feature);
            }
        }
    }
}
