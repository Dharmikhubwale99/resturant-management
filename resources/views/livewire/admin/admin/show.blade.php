<div>
    <div class="max-w-6xl mx-auto py-8 px-6">
        <x-form.error />

        <h2 class="text-2xl font-bold mb-4 text-gray-700 border-b pb-2">Personal Profile</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <x-form.input name="personal_name" label="Name" wireModel="personal_name" disabled/>
            <x-form.input name="personal_email" label="Email" type="email" wireModel="personal_email" disabled/>
            <x-form.input name="personal_mobile" label="Mobile" wireModel="personal_mobile" disabled/>

            <x-form.input name="personal_address" label="Address" wireModel="personal_address" disabled/>

            <x-form.input name="pincode" label="Pincode" type="text" wireModelLive="pincode" disabled/>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <x-form.input name="country_name" label="Country" type="text" wireModel="country_name" readonly disabled/>
            <x-form.input name="state_name" label="State" type="text" wireModel="state_name" readonly disabled/>
            <x-form.input name="city_name" label="City" type="text" wireModel="city_name" readonly disabled/>
            <x-form.input name="district_name" label="District" type="text" wireModel="district_name" readonly disabled/>
        </div>

        <h2 class="text-2xl font-bold mb-4 text-gray-700 border-b pb-2">Restaurant Profile</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <x-form.input name="restaurant_name" label="Restaurant Name" wireModel="restaurant_name" disabled/>
            <x-form.input name="restaurant_email" label="Restaurant Email" wireModel="restaurant_email" disabled/>
            <x-form.input name="restaurant_mobile" label="Restaurant Mobile" wireModel="restaurant_mobile" disabled/>

            <x-form.input name="restaurant_address" label="Restaurant Address" wireModel="restaurant_address" disabled/>
            <x-form.input name="gst" label="GST No" wireModel="gst" disabled/>
        </div>

        <h2 class="text-2xl font-bold mb-4 text-gray-700 border-b pb-2">Bank Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <x-form.input name="bank_name" label="Bank Name" wireModel="bank_name" disabled/>
            <x-form.input name="account_type" label="Account Type" wireModel="account_type" disabled/>

            <x-form.input name="holder_name" label="Holder Name" wireModel="holder_name" disabled/>

            <x-form.input name="account_number" label="Account Number" wireModel="account_number" disabled/>
            <x-form.input name="ifsc" label="IFSC" wireModel="ifsc" disabled/>
            <x-form.input name="upi_id" label="UPI ID" wireModel="upi_id" disabled/>
        </div>

        <h2 class="text-2xl font-bold mb-4 text-gray-700 border-b pb-2">Meta Settings</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <x-form.input name="meta_title" label="Meta Title" type="text" wireModel="meta_title" disabled />

            <x-form.input name="meta_keywords" label="Meta Keywords" type="text" wireModel="meta_keywords" disabled />

            <x-form.input name="meta_description" label="Meta Description" type="textarea" disabled
                wireModel="meta_description" />

            @if ($oldFavicon)
                <div class="mt-2">
                    <img src="{{ asset('storage/' . $oldFavicon) }}" class="ml-3 h-25 w-20" alt="Current Favicon">
                </div>
            @endif

            <x-form.select name="plan_id" label="Select Plan" :options="$plans" wireModel="plan_id"
                placeholder="-- Select Plan --"disabled  />
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <x-form.button :route="'superadmin.admin.index'" class="bg-gray-500 text-whitedisable">
                Back
            </x-form.button>
        </div>
    </div>
</div>
