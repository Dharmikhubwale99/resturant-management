<div>
    <div class="max-w-6xl mx-auto py-8 px-6">
        <x-form.error />

        <h2 class="text-2xl font-bold mb-4 text-gray-700 border-b pb-2">Personal Profile</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <x-form.input name="personal_name" label="Name" wireModel="personal_name" required />
            <x-form.input name="personal_email" label="Email" type="email" wireModel="personal_email" required />
            <x-form.input name="username" label="User Name" wireModel="username" required />
            <x-form.input name="personal_mobile" label="Mobile" wireModel="personal_mobile" required />

            <x-form.input name="personal_address" label="Address" wireModel="personal_address" />

            <x-form.input name="password" label="Password" type="password" wireModel="password" showToggle="true" />

            <x-form.input name="confirm_password" label="Confirm Password" type="password" wireModel="confirm_password"
            showToggle="true" />
            <x-form.input name="pincode" label="Pincode" type="text" wireModelLive="pincode" required />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <x-form.input name="country_name" label="Country" type="text" wireModel="country_name" readonly />
            <x-form.input name="state_name" label="State" type="text" wireModel="state_name" readonly />
            <x-form.input name="city_name" label="City" type="text" wireModel="city_name" readonly />
            <x-form.input name="district_name" label="District" type="text" wireModel="district_name" readonly />
        </div>

        <h2 class="text-2xl font-bold mb-4 text-gray-700 border-b pb-2">Restaurant Profile</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <x-form.input name="restaurant_name" label="Restaurant Name" wireModel="restaurant_name" required />
            <x-form.input name="restaurant_email" label="Restaurant Email" wireModel="restaurant_email" required />
            <x-form.input name="restaurant_mobile" label="Restaurant Mobile" wireModel="restaurant_mobile" required />

            <x-form.input name="restaurant_address" label="Restaurant Address" wireModel="restaurant_address" />
            <x-form.input name="gst" label="GST No" wireModel="gst" />
        </div>

        <h2 class="text-2xl font-bold mb-4 text-gray-700 border-b pb-2">Bank Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <x-form.input name="bank_name" label="Bank Name" wireModel="bank_name" />
            <x-form.select name="account_type" label="Account Type" :options="['savings' => 'Savings', 'current' => 'Current']" placeholder="-- Select Type --"
                wireModel="account_type" />
            <x-form.input name="holder_name" label="Holder Name" wireModel="holder_name" />

            <x-form.input name="account_number" label="Account Number" wireModel="account_number" />
            <x-form.input name="ifsc" label="IFSC" wireModel="ifsc" />
            <x-form.input name="upi_id" label="UPI ID" wireModel="upi_id" />
        </div>

        <h2 class="text-2xl font-bold mb-4 text-gray-700 border-b pb-2">Meta Settings</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <x-form.input name="meta_title" label="Meta Title" type="text" wireModel="meta_title" />

            <x-form.input name="meta_keywords" label="Meta Keywords" type="text" wireModel="meta_keywords" />

            <x-form.input name="meta_description" label="Meta Description" type="textarea"
                wireModel="meta_description" />

            <x-form.input name="favicon" label="Favicon Image" type="file" wireModel="favicon" />
            @if ($oldFavicon)
                <div class="mt-2">
                    <img src="{{ asset('storage/' . $oldFavicon) }}" class="ml-3 h-25 w-20" alt="Current Favicon">
                </div>
            @endif
        </div>

        <div class="mt-6 flex justify-end gap-3">
            <x-form.button title="Save Profile" wireClick="updateProfile" wireTarget="updateProfile" />
            <x-form.button :route="'restaurant.dashboard'" class="bg-gray-500 text-white">
                Back
            </x-form.button>
        </div>
    </div>
</div>
