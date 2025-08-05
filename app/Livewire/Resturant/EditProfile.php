<?php
namespace App\Livewire\Resturant;

use Livewire\Component;
use App\Models\{Restaurant, User, Country, State, City, District, PinCode, Setting};
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Http;

class EditProfile extends Component
{
    public $step = 1;

    // Step 1 - Personal Info
    public $personal_name, $personal_email, $personal_mobile, $personal_address;

    // Step 2 - Restaurant Info
    public $restaurant_name, $restaurant_email, $restaurant_mobile, $restaurant_address, $restaurant, $gst;

    // Step 3 - Bank Info
    public $bank_name, $ifsc, $holder_name, $account_type, $upi_id, $account_number;

    public $pincode, $pincode_id, $country_name, $state_name, $city_name, $district_name;
    public $country_id, $state_id, $city_id, $district_id;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.edit-profile');
    }
    public function mount()
    {
        $user = Auth::user();
        $this->restaurant = $user->restaurants->first();

        // Step 1
        $this->personal_name = $user->name;
        $this->personal_email = $user->email;
        $this->personal_mobile = $user->mobile;
        $this->personal_address = $user->address;

        // Step 2
        $this->restaurant_name = $this->restaurant?->name;
        $this->restaurant_email = $this->restaurant?->email;
        $this->restaurant_mobile = $this->restaurant?->mobile;
        $this->restaurant_address = $this->restaurant?->address;
        $this->gst = $this->restaurant?->gstin;

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

    public function nextStep()
    {
        if ($this->step === 1) {
            $this->validate([
                'personal_name' => 'required|string|max:255',
                'personal_email' => 'required|email',
                'personal_mobile' => 'required',
            ]);

            $user = Auth::user();
            $user->update([
                'name' => $this->personal_name,
                'email' => $this->personal_email,
                'mobile' => $this->personal_mobile,
                'address' => $this->personal_address,
                'pin_code_id' => $this->pincode_id,
            ]);
        }

        if ($this->step === 2) {
            $this->validate([
                'restaurant_name' => 'required|string|max:255',
                'restaurant_email' => 'required|email',
                'restaurant_mobile' => 'required',
            ]);

            $restaurant = Auth::user()->restaurant;
            if ($restaurant) {
                $restaurant->update([
                    'name' => $this->restaurant_name,
                    'email' => $this->restaurant_email,
                    'mobile' => $this->restaurant_mobile,
                    'address' => $this->restaurant_address,
                    'gstin' => $this->gst,
                ]);
            }
        }

        if ($this->step < 3) {
            $this->step++;
        }
    }

    public function previousStep()
    {
        if ($this->step > 1) {
            $this->step--;
        }
    }

    public function updateProfile()
    {
        $this->validate([
            'bank_name' => 'nullable|string|max:255',
            'ifsc' => 'nullable|string|max:20',
            'holder_name' => 'nullable|string|max:255',
            'account_type' => 'nullable|string|max:20',
            'upi_id' => 'nullable|string|max:50',
            'account_number' => 'nullable|string|max:50',
        ]);

        $this->restaurant->update([
            'bank_name' => $this->bank_name,
            'ifsc' => $this->ifsc,
            'holder_name' => $this->holder_name,
            'account_type' => $this->account_type,
            'upi_id' => $this->upi_id,
            'account_number' => $this->account_number,
        ]);

        session()->flash('success', 'Profile updated successfully!');
        return redirect()->route('restaurant.dashboard');
    }
}
