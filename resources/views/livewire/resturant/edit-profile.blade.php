<div>
    <div class="max-w-3xl mx-auto py-8 px-6">
        <x-form.error />

        @if ($step === 1)
            <h2 class="text-xl font-bold mb-6">Edit Personal Profile</h2>
            <div>
                <x-form.input name="personal_name" label="Name" wireModel="personal_name" required />
                <x-form.input name="personal_email" label="Email" type="email" wireModel="personal_email" required />
                <x-form.input name="password" label="Password" type="password" wireModel="password" showToggle="true"/>
                <x-form.input name="confirm_password" label="Confirm Password" type="password" wireModel="confirm_password" showToggle="true"  />
                <x-form.input name="personal_mobile" label="Mobile" wireModel="personal_mobile" required />
                <x-form.input name="personal_address" label="Address" wireModel="personal_address" />
                <x-form.input name="pincode" label="Pincode" type="text" wireModelLive="pincode" required />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="country_name" label="Country" type="text" wireModel="country_name" readonly />
                <x-form.input name="state_name" label="State" type="text" wireModel="state_name" readonly />
                <x-form.input name="city_name" label="City" type="text" wireModel="city_name" readonly />
                <x-form.input name="district_name" label="District" type="text" wireModel="district_name" readonly />
            </div>
            </div>
        @elseif ($step === 2)
        <h2 class="text-xl font-bold mb-6">Edit Resturant Profile</h2>
            <div>
                <x-form.input name="restaurant_name" label="Restaurant Name" wireModel="restaurant_name" required />
                <x-form.input name="restaurant_email" label="Restaurant Email" wireModel="restaurant_email" required />
                <x-form.input name="restaurant_mobile" label="Restaurant Mobile" wireModel="restaurant_mobile" required />
                <x-form.input name="restaurant_address" label="Restaurant Address" wireModel="restaurant_address" />
                <x-form.input name="gst" label="GST No" wireModel="gst" />
            </div>
        @elseif ($step === 3)
        <h2 class="text-xl font-bold mb-6">Edit Bank Details</h2>
            <div>
                <x-form.input name="bank_name" label="Bank Name" wireModel="bank_name" />
                <x-form.select
                    name="account_type"
                    label="Account Type"
                    :options="['savings' => 'Savings', 'current' => 'Current']"
                    placeholder="-- Select Type --"
                    wireModel="account_type"
                />
                <x-form.input name="holder_name" label="Holder Name" wireModel="holder_name" />
                <x-form.input name="account_number" label="Account Number" wireModel="account_number" />
                <x-form.input name="ifsc" label="IFSC" wireModel="ifsc" />
                <x-form.input name="upi_id" label="UPI ID" wireModel="upi_id" />
            </div>
        @endif

        {{-- Step navigation --}}
        <div class="mt-6 flex justify-between">
            @if($step > 1)
                <x-form.button title="Back" wireClick="previousStep" class="bg-gray-300 text-gray-800 hover:bg-gray-400" />
            @endif

            @if($step < 3)
                <x-form.button title="Next" wireClick="nextStep" wireTarget="nextStep" />
            @else
                <x-form.button title="Save" wireClick="updateProfile" wireTarget="updateProfile" />
            @endif
        </div>
    </div>

</div>
