<?php

namespace App\Livewire\Admin\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Models\{User, Restaurant, Country, State, City, District, PinCode, Setting, Plan, AppConfiguration, RestaurantConfiguration};
use Livewire\WithFileUploads;
use App\Traits\HasRolesAndPermissions;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Illuminate\Validation\Rule;

class Create extends Component
{
    use WithFileUploads, HasRolesAndPermissions;
    public $user_name, $email, $mobile, $password,$personal_address;
    public $pincode, $pincode_id, $country_name, $state_name, $city_name, $district_name, $username, $resto_mobile;
    public $country_id, $state_id, $city_id, $district_id;
    public $restaurant_name, $restaurant_address, $gst_no, $password_confirmation, $restaurant_email, $fssai;
    public $meta_title, $meta_description, $meta_keywords, $favicon, $oldFavicon;
    public $permissions = [];
    public $plan_id;
    public $plans = [];
    public $selected_plan_days;
    public $calculated_expiry;
    public $resto_pincode, $resto_pincode_id;
    public $resto_country_id, $resto_state_id, $resto_city_id, $resto_district_id;
    public $resto_country_name, $resto_state_name, $resto_city_name, $resto_district_name;


    #[Layout('components.layouts.admin.app')]
    public function render()
    {
        return view('livewire.admin.admin.create');
    }

    public function mount()
    {
        $this->plans = Plan::where('is_active', 0)->get()->mapWithKeys(function ($plan) {
            return [
                $plan->id => $plan->name . ' | ₹' . number_format($plan->price, 2) . ' | ' . $plan->duration_days . ' days'
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
                    'User-Agent' => 'Mozilla/5.0 (Linux; Android 10) AppleWebKit/537.36 Chrome/114.0.0.0 Safari/537.36'
                ])
                ->withOptions([
                    'curl' => [
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_FORBID_REUSE => true,
                        CURLOPT_FRESH_CONNECT => true,
                        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                    ]
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
            logger()->error("Pincode API error: " . $e->getMessage());
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

    public function updatedPlanId($value)
    {
        if ($value) {
            $plan = Plan::find($value);
            if ($plan) {
                $this->selected_plan_days = $plan->duration_days;
                $this->calculated_expiry = now()->addDays($plan->duration_days)->format('d-m-Y');
            }
        } else {
            $this->selected_plan_days = null;
            $this->calculated_expiry = null;
        }
    }

    public function updatedRestoPincode($value)
    {
        $cached = PinCode::with('district.city.state.country')->where('code', $value)->first();

        if ($cached) {
            $this->resto_pincode_id = $cached->id;
            $this->setRestoLocationFromModels(
                $cached->district->city->state->country,
                $cached->district->city->state,
                $cached->district->city,
                $cached->district
            );
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

                    $this->resto_pincode_id = $pincode->id;
                    $this->setRestoLocationFromModels($country, $state, $city, $district);
                } else {
                    session()->flash('message', 'Invalid pincode or no result found.');
                }
            } else {
                session()->flash('message', 'API request failed. Status: ' . $response->status());
            }
        } catch (\Illuminate\Http\Client\RequestException $e) {
            logger()->error('Resto Pincode API error: ' . $e->getMessage());
            session()->flash('message', 'Pincode service temporarily unavailable. Please try again later.');
        }
    }

    protected function setRestoLocationFromModels($country, $state, $city, $district)
    {
        $this->resto_country_id = $country->id;
        $this->resto_state_id   = $state->id;
        $this->resto_city_id    = $city->id;
        $this->resto_district_id= $district->id;

        $this->resto_country_name = $country->name;
        $this->resto_state_name   = $state->name;
        $this->resto_city_name    = $city->name;
        $this->resto_district_name= $district->name;
    }

    public function submit()
    {
        $this->validate([
            'user_name' => ['required','string','max:255'],

            'email' => [
                'required',
                'email',
                'regex:/^[\w\.\-]+@[\w\-]+\.(com)$/i',
                Rule::unique('users', 'email')->whereNull('deleted_at'),
            ],

            'mobile' => [
                'required',
                'digits:10',
                Rule::unique('users', 'mobile')->whereNull('deleted_at'),
            ],

            'username' => [
                'required','string','min:6','max:50',
                Rule::unique('users', 'username')->whereNull('deleted_at'),
            ],

            'password' => ['required','min:6','confirmed'],
            'pincode'  => ['required','digits:6'],
            'personal_address' => ['nullable','string','max:255'],

            'restaurant_name'    => ['nullable','string','max:255'],
            'restaurant_address' => ['nullable','string'],
            'resto_pincode'      => ['required','digits:6'],
            'restaurant_email'   => ['nullable','email','regex:/^[\w\.\-]+@[\w\-]+\.(com)$/i'],
            'resto_mobile'       => ['nullable','digits:10'],
            'gst_no'             => ['nullable','string','max:15'],
            'fssai'              => ['required','string','max:14'],

            'meta_title'       => ['nullable','string','max:255'],
            'meta_description' => ['nullable','string'],
            'meta_keywords'    => ['nullable','string'],
            'favicon'          => ['nullable','file','max:1024'],
            'plan_id'          => ['nullable','exists:plans,id'],
        ]);


        if ($this->plan_id) {
            $selectedPlan = Plan::find($this->plan_id);
            $planId = $selectedPlan->id;
            $expiryDate = now()->addDays($selectedPlan->duration_days ?? 30);
            $fileStorage = $selectedPlan->storage_quota_mb ?? 500;
            $maxUploadSize = $selectedPlan->max_file_size_kb ?? 10;
        } else {
            $selectedPlan = null;
            $planId = null;
            $expiryDate = null;
        }

        if (!$this->pincode_id && $this->pincode) {
            $pin = PinCode::with('district.city.state.country')
                    ->where('code', $this->pincode)
                    ->first();
            if ($pin) {
                $this->pincode_id = $pin->id;
            } else {
                $this->addError('pincode', 'Enter a valid pincode.');
                return;
            }
        }

        if (!$this->resto_pincode_id && $this->resto_pincode) {
            $rp = PinCode::with('district.city.state.country')
                    ->where('code', $this->resto_pincode)
                    ->first();
            if ($rp) {
                $this->resto_pincode_id = $rp->id;
            } else {
                $this->addError('resto_pincode', 'Enter a valid restaurant pincode.');
                return;
            }
        }

        $faviconPath = $this->oldFavicon;

        if ($this->favicon) {
            $faviconPath = $this->favicon->store('icon', 'public');
        }

        $admin = auth()->user();

        $user = User::create([
            'name' => $this->user_name,
            'email' => $this->email,
            'username' => $this->username,
            'mobile' => $this->mobile,
            'password' => Hash::make($this->password),
            'pin_code_id' => $this->pincode_id,
            'is_active' =>  0,
            'referred_by' => $admin->id,
        ]);

        $user->assignRole('admin');
        $permissions = $this->getAllPermissions();
        foreach ($permissions as $perm) {
            Permission::firstOrCreate(['name' => $perm]);
        }
        $user->givePermissionTo($permissions);

        $restaurant = Restaurant::create([
            'user_id' => $user->id,
            'pin_code_id' => $this->resto_pincode_id,
            'name' => $this->restaurant_name,
            'address' => $this->restaurant_address,
            'email' => $this->restaurant_email,
            'mobile' => $this->resto_mobile,
            'gstin' => $this->gst_no,
            'fssai' => $this->fssai,
            'plan_id' => $planId,
            'plan_expiry_at' => $expiryDate,
            'storage_quota_mb' => $fileStorage ?? 500,
            'max_file_size_kb' => $maxUploadSize ?? 10,
        ]);

        Setting::create([
            'user_id' => $user->id,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'favicon' => $faviconPath,
        ]);


        if($this->plan_id) {
            $this->syncRestaurantFeatures($restaurant, $selectedPlan);
        }

        session()->flash('success', 'User Restaurant created successfully.');
        return redirect()->route('superadmin.admin.index');
    }

    protected function syncRestaurantFeatures($restaurant, $plan)
    {
        Log::info('Syncing restaurant features for plan: ' . $plan->name);
        Log::info('Restaurant ID: ' . $restaurant->id);

        RestaurantConfiguration::where('restaurant_id', $restaurant->id)->delete();

        $configMap = AppConfiguration::pluck('id', 'key')->toArray();

        foreach ($plan->planFeatures as $feature) {
            $configId = $configMap[$feature->feature] ?? null;

            if ($configId) {
                RestaurantConfiguration::create([
                    'restaurant_id'    => $restaurant->id,
                    'configuration_id' => $configId,
                    'value'            => $feature->is_active ? 1 : 0,
                ]);
            } else {
                Log::warning('No AppConfiguration found for feature: ' . $feature->feature);
            }
        }
    }

}
