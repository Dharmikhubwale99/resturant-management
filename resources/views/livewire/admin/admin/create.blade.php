<div class="max-w-6xl mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-6">Create Restaurant & User</h2>
    <x-form.error />

    <form wire:submit.prevent="submit" class="space-y-8">

        <div class="rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b">User Information</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-form.input name="user_name" label="Name" type="text" wireModel="user_name" required />
                <x-form.input name="email" label="Email" type="email" wireModel="email" required />
                <x-form.input name="username" label="User Name" type="text" wireModel="username" required />
                <x-form.input name="mobile" label="Mobile" type="tel" wireModel="mobile" maxlength="10"
                    oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)" required />
                <x-form.input name="password" label="Password" type="password" wireModel="password" required showToggle="true" />
                <x-form.input name="password_confirmation" label="Confirm Password" type="password"
                    wireModel="password_confirmation" required showToggle="true" />
                    <x-form.input name="pincode" label="Pincode" type="text" wireModelLive="pincode" required />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                    <x-form.input name="country_name" label="Country" type="text" wireModel="country_name" readonly />
                    <x-form.input name="state_name" label="State" type="text" wireModel="state_name" readonly />
                <x-form.input name="city_name" label="City" type="text" wireModel="city_name" readonly />
                <x-form.input name="district_name" label="District" type="text" wireModel="district_name" readonly />
            </div>
            <x-form.input name="personal_address" label="Address" type="textarea" wireModel="personal_address" />

        </div>

        <div class="rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b">Restaurant Information</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-form.input name="restaurant_name" label="Restaurant Name" type="text" wireModel="restaurant_name" />
                <x-form.input name="restaurant_email" label="Restaurant Email" type="email" wireModel="restaurant_email" />
                <x-form.input name="resto_mobile" label="Restaurant Mobile" type="tel" wireModel="resto_mobile" maxlength="10"
                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)" />
                <x-form.input name="gst_number" label="GST Number" type="text" wireModel="gst_no" />
                <x-form.input name="fssai" label="FSSAI Number" type="text" wireModel="fssai" required />
                <x-form.input name="resto_pincode" label="Restaurant Pincode" type="text" wireModelLive="resto_pincode" required />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
                <x-form.input name="resto_country_name" label="Country" type="text" wireModel="resto_country_name" readonly />
                <x-form.input name="resto_state_name" label="State" type="text" wireModel="resto_state_name" readonly />
                <x-form.input name="resto_city_name" label="City" type="text" wireModel="resto_city_name" readonly />
                <x-form.input name="resto_district_name" label="District" type="text" wireModel="resto_district_name" readonly />
            </div>
            <x-form.input name="restaurant_address" label="Address" type="textarea" wireModel="restaurant_address" class="md:col-span-2" />
        </div>

        <div class="rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b">Restaurant Information</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-form.input name="meta_title" label="Meta Title" type="text" wireModel="meta_title" />
                <x-form.input name="meta_keywords" label="Meta Keywords" type="text" wireModel="meta_keywords" />
                <x-form.input name="favicon" label="Favicon Image" type="file" wireModel="favicon" />
                <x-form.input name="meta_description" label="Meta Description" type="textarea" wireModel="meta_description" class="md:col-span-2" />
            </div>
        </div>

        <x-form.select name="plan_id" label="Select Plan" :options="$plans" wireModelLive="plan_id"
            placeholder="-- Select Plan --" />

        @if ($calculated_expiry && $selected_plan_days)
            <div class="text-green-600 font-semibold mt-4">
                Expiry Date: {{ $calculated_expiry }} ({{ $selected_plan_days }} days)
            </div>
        @endif

        <div class="flex justify-start gap-4">
            <x-form.button type="submit" title="Save" wireTarget="submit" />
            <x-form.button title="Back" class="bg-gray-500 hover:bg-gray-600 text-white"
                route="superadmin.admin.index" />
        </div>
    </form>
</div>
