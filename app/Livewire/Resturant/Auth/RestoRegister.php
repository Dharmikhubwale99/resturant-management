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
    public $email;
    public $mobile, $resto_mobile, $personal_address;
    public $restaurant_name, $address, $gst;
    public $pincode, $pincode_id, $country_name, $state_name, $city_name, $district_name;
    public $country_id, $state_id, $city_id, $district_id;
    public $meta_title, $meta_description, $meta_keywords, $favicon, $oldFavicon;
    public $bank_name, $ifsc, $holder_name, $account_type, $upi_id, $account_number;

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
            $this->personal_address = $user->address;
        }

        if ($restaurant) {
            $this->restaurant_name = $restaurant->name;
            $this->address = $restaurant->address;
            $this->gst = $restaurant->gstin;
            $this->pincode = $user->pin_code_id;
            $this->resto_mobile = $restaurant->mobile;
            $this->bank_name = $restaurant->bank_name;
            $this->ifsc = $restaurant->ifsc;
            $this->holder_name = $restaurant->holder_name;
            $this->account_type = $restaurant->account_type;
            $this->upi_id = $restaurant->upi_id;
            $this->account_number = $restaurant->account_number;

            if ($user->pin_code_id) {
                $pincode = \App\Models\PinCode::with('district.city.state.country')->find($user->pin_code_id);

                if ($pincode) {
                    $this->pincode_id = $pincode->id;
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

        if ($settings) {
            $this->meta_title = $settings->meta_title;
            $this->meta_description = $settings->meta_description;
            $this->meta_keywords = $settings->meta_keywords;
            $this->favicon = $settings->favicon;
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

    public function register()
    {
        $validated = $this->validate(
            [
                'restaurant_name' => 'required|string|max:255',
                'mobile' => ['regex:/^[0-9]{10}$/'],
                'address' => 'nullable|string|max:255',
                'gst' => 'nullable|string|max:15',
                'pincode' => 'required|digits:6',
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
                'email' => [
                    'required',
                    'email',
                    'regex:/^[\w\.\-]+@[\w\-]+\.(com)$/i',
                    Rule::unique('users', 'email')->ignore(Auth::id())->whereNull('deleted_at'),
                ],
            ],
            [
                'email.regex' => 'Only .com email addresses are allowed.',
            ],
        );

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
            'address' => $this->personal_address,
            'pin_code_id' => $this->pincode_id,
        ]);

        $restaurant = Restaurant::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $this->restaurant_name,
                'email' => $this->email,
                'mobile' => $this->mobile,
                'address' => $this->address,
                'gstin' => $this->gst,
                'pin_code_id' => $this->pincode_id,
                'bank_name' => $this->bank_name,
                'ifsc' => $this->ifsc,
                'holder_name' => $this->holder_name,
                'account_type' => $this->account_type,
                'upi_id' => $this->upi_id,
                'account_number' => $this->account_number,
            ],
        );

        Setting::updateOrCreate([
            'user_id' => $user->id,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'favicon' => $faviconPath,
        ]);

        Auth::login($user);
        return redirect()->route('restaurant.dashboard')->with('success', 'Restaurant registered successfully.');
    }
}
