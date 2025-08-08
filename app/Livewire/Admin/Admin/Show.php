<?php

namespace App\Livewire\Admin\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\User;

class Show extends Component
{
    public $personal_name, $personal_email, $personal_mobile, $personal_address;
    public $restaurant_name, $restaurant_email, $restaurant_mobile, $restaurant_address, $restaurant, $gst;
    public $bank_name, $ifsc, $holder_name, $account_type, $upi_id, $account_number;
    public $pincode, $pincode_id, $country_name, $state_name, $city_name, $district_name;
    public $country_id, $state_id, $city_id, $district_id;
    public $meta_title, $meta_description, $meta_keywords, $favicon, $oldFavicon;

    #[Layout('components.layouts.admin.app')]
    public function render()
    {
        return view('livewire.admin.admin.show');
    }

    public function mount($id)
    {
        $user = User::where('id', $id)->with('restaurants')->firstOrFail();
        $this->restaurant = $user->restaurants->first();
        $setting = $user->setting;

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

        if ($setting) {
            $this->meta_title = $setting->meta_title;
            $this->meta_description = $setting->meta_description;
            $this->meta_keywords = $setting->meta_keywords;
            $this->favicon = $setting->favicon;
            $this->oldFavicon = $setting->favicon;
        }
    }
}
