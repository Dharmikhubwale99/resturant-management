<?php

namespace App\Livewire\Resturant\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\{Restaurant, User, Country, State, City, District, PinCode, Setting};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class RestoRegister extends Component
{
    use WithFileUploads;

    public $name;
    public $email, $username;
    public $mobile, $resto_mobile, $personal_address;
    public $restaurant_name, $restaurant_email, $address, $gst, $fssai;
    public $pincode, $pincode_id, $country_name, $state_name, $city_name, $district_name;
    public $country_id, $state_id, $city_id, $district_id;
    public $meta_title, $meta_description, $meta_keywords, $favicon, $oldFavicon;
    public $bank_name, $ifsc, $holder_name, $account_type, $upi_id, $account_number;
    public $resto_pincode, $resto_pincode_id;
    public $resto_country_id, $resto_state_id, $resto_city_id, $resto_district_id;
    public $resto_country_name, $resto_state_name, $resto_city_name, $resto_district_name;


    #[Layout('components.layouts.auth.app')]
    public function render()
    {
        return view('livewire.resturant.auth.resto-register');
    }

    public function mount()
    {
        $user = Auth::user();
        $restaurant = Restaurant::firstWhere('user_id', $user->id);
        $settings = Setting::firstWhere('user_id', $user->id);

        if($user) {
            $this->name = $user->name;
            $this->mobile = $user->mobile;
            $this->email = $user->email;
            $this->username = $user->username;
            $this->personal_address = $user->address;
            if ($user->pin_code_id) {
                $pincode = \App\Models\PinCode::with('district.city.state.country')->find($user->pin_code_id);

                if ($pincode) {
                    $this->pincode = $pincode->code;
                    $this->country_name = $pincode->district->city->state->country->name ?? '';
                    $this->state_name = $pincode->district->city->state->name ?? '';
                    $this->city_name = $pincode->district->city->name ?? '';
                    $this->district_name = $pincode->district->name ?? '';

                    $this->country_id = $pincode->district->city->state->country->id ?? null;
                    $this->state_id = $pincode->district->city->state->id ?? null;
                    $this->city_id = $pincode->district->city->id ?? null;
                    $this->district_id = $pincode->district->id ?? null;
                }
            }
        }

        if ($restaurant) {
            $this->restaurant_name = $restaurant->name;
            $this->restaurant_email = $restaurant->email;
            $this->address = $restaurant->address;
            $this->gst = $restaurant->gstin;
            $this->resto_mobile = $restaurant->mobile;
            $this->bank_name = $restaurant->bank_name;
            $this->ifsc = $restaurant->ifsc;
            $this->holder_name = $restaurant->holder_name;
            $this->account_type = $restaurant->account_type;
            $this->upi_id = $restaurant->upi_id;
            $this->account_number = $restaurant->account_number;
            $this->fssai = $restaurant->fssai;

            if ($restaurant->pin_code_id) {
                $rp = PinCode::with('district.city.state.country')->find($restaurant->pin_code_id);
                if ($rp) {
                    $this->resto_pincode_id = $rp->id;
                    $this->resto_pincode = $rp->code;
                    $this->resto_country_name = $rp->district->city->state->country->name ?? '';
                    $this->resto_state_name = $rp->district->city->state->name ?? '';
                    $this->resto_city_name = $rp->district->city->name ?? '';
                    $this->resto_district_name = $rp->district->name ?? '';

                    $this->resto_country_id = $rp->district->city->state->country->id ?? null;
                    $this->resto_state_id = $rp->district->city->state->id ?? null;
                    $this->resto_city_id = $rp->district->city->id ?? null;
                    $this->resto_district_id = $rp->district->id ?? null;
                }
            }
        }

        if ($settings) {
            $this->meta_title = $settings->meta_title;
            $this->meta_description = $settings->meta_description;
            $this->meta_keywords = $settings->meta_keywords;
            $this->favicon = null;
            $this->oldFavicon = $settings->favicon;
        }
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


    public function register()
    {
        $validated = $this->validate(
            [
                'restaurant_name' => 'required|string|max:255',
                'mobile' => ['regex:/^[0-9]{10}$/'],
                'address' => 'nullable|string|max:255',
                'gst' => 'nullable|string|max:15',
                'pincode' => 'required|digits:6',
                'email' => [
                    'required',
                    'email',
                    'regex:/^[\w\.\-]+@[\w\-]+\.(com)$/i',
                    Rule::unique('users', 'email')->ignore(Auth::id())->whereNull('deleted_at'),
                ],
                'username' => [
                    'required',
                    'string',
                    'max:255',
                    Rule::unique('users', 'username')->ignore(Auth::id())->whereNull('deleted_at'),
                ],

                'restaurant_name' => 'required|string|max:255',
                'resto_pincode' => 'required|digits:6',
                'restaurant_email' => [
                    'nullable',
                    'email',
                    'regex:/^[\w\.\-]+@[\w\-]+\.(com)$/i',
                ],
                'resto_mobile' => ['regex:/^[0-9]{10}$/'],
                'fssai' => 'required|string|max:14',

                'meta_title' => 'nullable|string|max:255',
                'meta_description' => 'nullable|string',
                'meta_keywords' => 'nullable|string',
                'favicon' => 'nullable|file|max:1024',
                'bank_name' => 'nullable|string|max:255',
                'ifsc' => 'nullable|string|max:20',
                'holder_name' => 'nullable|string|max:255',
                'account_type' => 'nullable|string|max:20',
                'upi_id' => 'nullable|string|max:50',
                'account_number' => 'nullable|string|max:50',
            ],
            [
                'email.regex' => 'Only .com email addresses are allowed.',
            ],
        );

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
            if ($this->oldFavicon && Storage::disk('public')->exists($this->oldFavicon)) {
                Storage::disk('public')->delete($this->oldFavicon);
            }
            $faviconPath = $this->favicon->store('icon', 'public');
        }

        $user = Auth::user();

        $user->update([
            'name' => $this->name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'username' => $this->username,
            'address' => $this->personal_address,
            'pin_code_id' => $this->pincode_id,
        ]);

        $restaurant = Restaurant::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $this->restaurant_name,
                'email' => $this->restaurant_email,
                'mobile' => $this->resto_mobile,
                'address' => $this->address,
                'gstin' => $this->gst,
                'pin_code_id' => $this->resto_pincode_id,
                'bank_name' => $this->bank_name,
                'ifsc' => $this->ifsc,
                'holder_name' => $this->holder_name,
                'account_type' => $this->account_type,
                'upi_id' => $this->upi_id,
                'account_number' => $this->account_number,
                'fssai' => $this->fssai,
            ],
        );

        Setting::updateOrCreate(
            ['user_id' => $user->id],
            [
                'meta_title'       => $this->meta_title,
                'meta_description' => $this->meta_description,
                'meta_keywords'    => $this->meta_keywords,
                'favicon'          => $faviconPath,
            ]
        );

        Auth::login($user);
        return to_route('restaurant.dashboard')->with('success', 'Restaurant registered successfully.');
    }
}
