<?php
namespace App\Livewire\Resturant;

use Livewire\Component;
use App\Models\{Restaurant, User, Country, State, City, District, PinCode, Setting};
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class EditProfile extends Component
{
    use WithFileUploads;

    public $personal_name, $personal_email, $personal_mobile, $personal_address;
    public $password, $confirm_password;

    public $restaurant_name, $restaurant_email, $restaurant_mobile, $restaurant_address, $restaurant, $gst;

    public $bank_name, $ifsc, $holder_name, $account_type, $upi_id, $account_number;

    public $pincode, $pincode_id, $country_name, $state_name, $city_name, $district_name;
    public $country_id, $state_id, $city_id, $district_id;
    public $meta_title, $meta_description, $meta_keywords, $favicon, $oldFavicon, $setting;

    #[Layout('components.layouts.resturant.app')]
    public function render()
    {
        return view('livewire.resturant.edit-profile');
    }
    public function mount()
    {
        $user = Auth::user();
        $this->restaurant = $user->restaurants->first();
        $this->setting = $user->setting;

        $this->personal_name = $user->name;
        $this->personal_email = $user->email;
        $this->personal_mobile = $user->mobile;
        $this->personal_address = $user->address;

        $this->restaurant_name = $this->restaurant?->name;
        $this->restaurant_email = $this->restaurant?->email;
        $this->restaurant_mobile = $this->restaurant?->mobile;
        $this->restaurant_address = $this->restaurant?->address;
        $this->gst = $this->restaurant?->gstin;
        $this->bank_name = $this->restaurant?->bank_name;
        $this->ifsc = $this->restaurant?->ifsc;
        $this->holder_name = $this->restaurant?->holder_name;
        $this->account_type = $this->restaurant?->account_type;
        $this->upi_id = $this->restaurant?->upi_id;
        $this->account_number = $this->restaurant?->account_number;

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

        if ($this->setting) {
            $this->meta_title = $this->setting->meta_title;
            $this->meta_description = $this->setting->meta_description;
            $this->meta_keywords = $this->setting->meta_keywords;
            $this->favicon = $this->setting->favicon;
            $this->oldFavicon = $this->setting->favicon;
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


    public function updateProfile()
    {
        $this->validate([
            'personal_name' => 'required|string|max:255',
            'personal_email' => 'required|email',
            'personal_mobile' => 'required',

            'restaurant_name' => 'required|string|max:255',
            'restaurant_email' => 'required|email',
            'restaurant_mobile' => 'required',

            'bank_name' => 'nullable|string|max:255',
            'ifsc' => 'nullable|string|max:20',
            'holder_name' => 'nullable|string|max:255',
            'account_type' => 'nullable|string|max:20',
            'upi_id' => 'nullable|string|max:50',
            'account_number' => 'nullable|string|max:50',

            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'favicon' => 'nullable',
        ]);

        $user = Auth::user();

        $hashedPassword = $user->password;
        if ($this->password) {
            $this->validate([
                'password' => 'required|min:6',
                'confirm_password' => 'required|same:password',
            ]);
            $hashedPassword = Hash::make($this->password);
        }

        $user->update([
            'name' => $this->personal_name,
            'email' => $this->personal_email,
            'mobile' => $this->personal_mobile,
            'address' => $this->personal_address,
            'pin_code_id' => $this->pincode_id,
            'password' => $hashedPassword
        ]);

        if ($this->restaurant) {
            $this->restaurant->update([
                'name' => $this->restaurant_name,
                'email' => $this->restaurant_email,
                'mobile' => $this->restaurant_mobile,
                'address' => $this->restaurant_address,
                'gstin' => $this->gst,
                'bank_name' => $this->bank_name,
                'ifsc' => $this->ifsc,
                'holder_name' => $this->holder_name,
                'account_type' => $this->account_type,
                'upi_id' => $this->upi_id,
                'account_number' => $this->account_number,
            ]);
        }

        $faviconPath = $this->oldFavicon;
        if ($this->favicon && $this->favicon !== $this->oldFavicon) {
            if ($this->oldFavicon && Storage::disk('public')->exists($this->oldFavicon)) {
                Storage::disk('public')->delete($this->oldFavicon);
            }

            $faviconPath = $this->favicon->store('icon', 'public');
        } elseif ($this->favicon === null) {
            $faviconPath = null;
        }

        if ($this->setting) {
            $this->setting->update([
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'meta_keywords' => $this->meta_keywords,
                'favicon' => $faviconPath,
            ]);
        }

        session()->flash('success', 'Profile updated successfully!');
        return redirect()->route('restaurant.dashboard');
    }

    // public function updatePassword()
    // {
    //     $this->validate([
    //         'password' => 'nullable|string|min:6',
    //         'confirm_password' => 'nullable|same:password',
    //     ]);

    //     $user = Auth::user();
    //     $user->update([
    //         'password' => Hash::make($this->password),
    //     ]);

    //     $this->reset(['password', 'confirm_password']);
    //     session()->flash('success', 'Password updated successfully!');
    //     return redirect()->route('restaurant.dashboard');
    // }
}
