<?php

namespace App\Livewire\Admin\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use App\Models\{User, Restaurant, Country, State, City, District, PinCode, Setting};
use Livewire\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;
    public $user_name, $email, $mobile, $password;
    public $pincode, $pincode_id, $country_name, $state_name, $city_name, $district_name;
    public $country_id, $state_id, $city_id, $district_id;
    public $restaurant_name, $restaurant_address, $gst_no, $password_confirmation;
    public $meta_title, $meta_description, $meta_keywords, $favicon, $oldFavicon;

    #[Layout('components.layouts.superadmin.app')]
    public function render()
    {
        return view('livewire.admin.admin.create');
    }

    public function updatedPincode($value)
    {
        $cached = PinCode::with('district.city.state.country')
            ->where('code', $value)
            ->first();

            if ($cached) {
                $this->pincode_id = $cached->id;
                $this->setLocationFromModels(
                    $cached->district->city->state->country,
                    $cached->district->city->state,
                    $cached->district->city,
                    $cached->district
                );
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

    public function submit()
    {
        $this->validate([
            'user_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile' => ['required', 'regex:/^[0-9]{10}$/'],
            'password' => 'required|min:6|confirmed',
            'pincode' => 'required|digits:6',
            'restaurant_name' => 'required|string|max:255',
            'restaurant_address' => 'nullable|string',
            'gst_no' => 'nullable|string|max:15',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'favicon' => 'nullable|image|max:1024',
        ]);

        $faviconPath = $this->oldFavicon;

        if ($this->favicon) {
            $faviconPath = $this->favicon->store('icon', 'public');
        }

        $user = User::create([
            'name' => $this->user_name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'password' => Hash::make($this->password),
            'pin_code_id' => $this->pincode_id,
            'is_active' =>  0,
        ]);

        $user->assignRole('admin');

        Restaurant::create([
            'user_id' => $user->id,
            'pin_code_id' => $this->pincode_id,
            'name' => $this->restaurant_name,
            'address' => $this->restaurant_address,
            'gstin' => $this->gst_no,
        ]);

        Setting::create([
            'user_id' => $user->id,
            'meta_title' => $this->meta_title,
            'meta_description' => $this->meta_description,
            'meta_keywords' => $this->meta_keywords,
            'favicon' => $faviconPath,
        ]);

        session()->flash('success', 'User Restaurant created successfully.');
        return redirect()->route('superadmin.admin.index');
    }
}
