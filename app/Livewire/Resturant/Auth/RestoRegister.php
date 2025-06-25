<?php

namespace App\Livewire\Resturant\Auth;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\{Restaurant, User, Country, State, City, District, PinCode};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class RestoRegister extends Component
{
    public $name;
    public $email;
    public $mobile;
    public $restaurant_name, $address, $gst;
    public $pincode, $pincode_id, $country_name, $state_name, $city_name, $district_name;
    public $country_id, $state_id, $city_id, $district_id;

    #[Layout('components.layouts.auth.app')]
    public function render()
    {
        return view('livewire.resturant.auth.resto-register');
    }

    public function updatedPincode($value)
    {
        $cached = PinCode::with('district.city.state.country')->where('code', $value)->first();

        if ($cached) {
            $this->pincode_id = $cached->id;
            $this->setLocationFromModels($cached->district->city->state->country, $cached->district->city->state, $cached->district->city, $cached->district);
            return;
        }

        $response = Http::get("https://api.postalpincode.in/pincode/{$value}");

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
            }
        } else {
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
        $validated = $this->validate([
            'restaurant_name' => 'required|string|max:255',
            'mobile' => ['required', 'regex:/^[0-9]{10}$/'],
            'address' => 'required|string|max:255',
            'gst' => 'nullable|string|max:15',
            'pincode' => 'required|digits:6',
            'email' => [
                'required',
                'email',
                'unique:users,email',
                'regex:/^[\w\.\-]+@[\w\-]+\.(com)$/i',
            ],
        ], [
            'email.regex' => 'Only .com email addresses are allowed.',
        ]);

        $user = Auth::user();
        $restaurant = Restaurant::updateOrCreate(
            ['user_id' => $user->id],
            [
                'name' => $this->restaurant_name,
                'email' => $this->email,
                'mobile' => $this->mobile,
                'address' => $this->address,
                'gstin' => $this->gst,
                'pin_code_id' => $this->pincode_id,
            ],
        );

        Auth::login($user);
        return redirect()->route('resturant.dashboard')->with('success', 'Restaurant registered successfully.');
    }
}
