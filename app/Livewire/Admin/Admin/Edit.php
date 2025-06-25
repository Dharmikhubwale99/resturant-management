<?php

namespace App\Livewire\Admin\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Illuminate\Support\Facades\Hash;
use App\Models\{User, Restaurant, Country, State, City, District, PinCode, Setting};
use Livewire\WithFileUploads;

class Edit extends Component
{
    use WithFileUploads;

    public $user_id, $restaurant_id, $setting_id;
    public $user_name, $email, $mobile;
    public $pincode, $pincode_id, $country_name, $state_name, $city_name, $district_name;
    public $country_id, $state_id, $city_id, $district_id;
    public $restaurant_name, $restaurant_address, $gst_no;
    public $meta_title, $meta_description, $meta_keywords, $favicon, $oldFavicon;

    #[Layout('components.layouts.superadmin.app')]
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
        }

        if($setting) {
            $this->meta_title = $setting->meta_title;
            $this->meta_description = $setting->meta_description;
            $this->meta_keywords = $setting->meta_keywords;
            $this->favicon = $setting->favicon;
            $this->oldFavicon = $setting->favicon;
        }
    }

    public function updatedPincode($value)
    {
        $cached = PinCode::with('district.city.state.country')
            ->where('code', $value)
            ->first();

        if ($cached) {
            $this->pincode_id = $cached->id;
            $this->setLocationFromModels($cached->district->city->state->country, $cached->district->city->state, $cached->district->city, $cached->district);
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
            'restaurant_name' => 'required|string|max:255',
            'restaurant_address' => 'nullable|string',
            'gst_no' => 'nullable|string|max:15',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
            'favicon' => 'nullable|image|max:1024',
        ]);

        $user = User::findOrFail($this->user_id);
        $user->update([
            'name' => $this->user_name,
            'email' => $this->email,
            'mobile' => $this->mobile,
            'pin_code_id' => $this->pincode_id,
        ]);

        if ($this->restaurant_id) {
            Restaurant::where('id', $this->restaurant_id)->update([
                'name' => $this->restaurant_name,
                'address' => $this->restaurant_address,
                'gstin' => $this->gst_no,
                'pin_code_id' => $this->pincode_id,
            ]);
        }

        $faviconPath = $this->oldFavicon;
        if ($this->favicon && $this->favicon !== $this->oldFavicon) {
            $faviconPath = $this->favicon->store('icon', 'public');
        } else if ($this->favicon === null) {
            $faviconPath = null; // Handle case where favicon is removed
        }

        if($this->setting_id) {
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

        session()->flash('success', 'User & Restaurant updated successfully.');
        return redirect()->route('superadmin.admin.index');
    }
}
