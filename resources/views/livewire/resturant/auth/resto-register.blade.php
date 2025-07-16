<div class="p-6 max-w-3xl mx-auto bg-white shadow rounded mt-10 mb-10">
    <div class="mt-1">
        <h2 class="text-2xl font-bold mb-6">Restaurant Registration</h2>
        <x-form.error />

        <form wire:submit.prevent="register" class="space-y-5">

            <h3 class="text-lg font-semibold text-gray-700">User Information</h3>

            <x-form.input name="name" label="Resturant Name" type="text" wireModel="name" required />

            <x-form.input name="email" label="Email" type="email" wireModel="email" readonly/>

            <x-form.input name="mobile" label="Mobile" type="tel" wireModel="mobile" maxlength="10"
                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)" readonly />

            <x-form.input name="gst" label="GST Number" type="text" wireModel="gst" />

            <x-form.input name="pincode" label="Pincode" type="text" wireModelLive="pincode" required />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="country_name" label="Country" type="text" wireModel="country_name" readonly />
                <x-form.input name="state_name" label="State" type="text" wireModel="state_name" readonly />
                <x-form.input name="city_name" label="City" type="text" wireModel="city_name" readonly />
                <x-form.input name="district_name" label="District" type="text" wireModel="district_name" readonly />
            </div>

            <h3 class="text-lg font-semibold text-gray-700 mt-6">Restaurant Information</h3>

            <x-form.input name="restaurant_name" label="Restaurant Name" type="text" wireModel="restaurant_name"
                required />

            <x-form.input name="mobile" label="Restaurant Mobile" type="tel" wireModel="resto_mobile" maxlength="10"
                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)" readonly />


            <x-form.input name="address" label="Address" type="textarea" wireModel="address" />

            <x-form.input name="meta_title" label="Meta Title" type="text" wireModel="meta_title" />

            <x-form.input name="meta_description" label="Meta Description" type="textarea"
                wireModel="meta_description" />

            <x-form.input name="meta_keywords" label="Meta Keywords" type="text" wireModel="meta_keywords" />

            <x-form.input name="favicon" label="Favicon Image" type="file" wireModel="favicon" />
            @if ($oldFavicon)
                <div class="mb-2">
                    <p class="text-sm">Current Favicon:</p>
                    <img src="{{ asset('storage/' . $oldFavicon) }}" class="w-20 h-20 rounded" />
                </div>
            @endif


            <div class="flex justify-start space-x-4">
                <x-form.button type="submit" title="Register" wireTarget="register" />
                <x-form.button title="Back" class="bg-gray-500 hover:bg-gray-600 text-white" route="login" />
            </div>

        </form>
    </div>
</div>
