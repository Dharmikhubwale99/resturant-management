<div class="p-6 max-w-3xl mx-auto bg-white shadow rounded mt-10 mb-10">
    <div class="mt-1">
        <h2 class="text-2xl font-bold mb-6">Create Restaurant & User</h2>
        <x-form.error />
        <form wire:submit.prevent="submit" class="space-y-5">
            <h3 class="text-lg font-semibold text-gray-700">User Information</h3>

            <x-form.input name="user_name" label="Name" type="text" wireModel="user_name" required />

            <x-form.input name="email" label="Email" type="email" wireModel="email" required />

            <x-form.input name="mobile" label="Mobile" type="tel" wireModel="mobile" maxlength="10"
                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)" required />

            <x-form.input name="password" label="Password" type="password" wireModel="password" required
                showToggle="true" />

            <x-form.input name="password_confirmation" label="Confirm Password" type="password"
                wireModel="password_confirmation" required showToggle="true" />

            <x-form.input name="gst_number" label="GST Number" type="text" wireModel="gst_no" />

            <x-form.input name="pincode" label="Pincode" type="text" wireModelLive="pincode" required />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-form.input name="country_name" label="Country" type="text" wireModel="country_name" readonly />

                <x-form.input name="state_name" label="State" type="text" wireModel="state_name" readonly />

                <x-form.input name="city_name" label="City" type="text" wireModel="city_name" readonly />

                <x-form.input name="district_name" label="District" type="text" wireModel="district_name" readonly />
            </div>

            <h3 class="text-lg font-semibold text-gray-700 mt-6">Restaurant Information</h3>

            <x-form.input name="restaurant_name" label="Restaurant Name" type="text" wireModel="restaurant_name" />

            <x-form.input name="restaurant_address" label="Address" type="textarea" wireModel="restaurant_address" />

            <x-form.input name="meta_title" label="Meta Title" type="text" wireModel="meta_title" />

            <x-form.input name="meta_description" label="Meta Description" type="textarea"
                wireModel="meta_description" />

            <x-form.input name="meta_keywords" label="Meta Keywords" type="text" wireModel="meta_keywords" />

            <x-form.input name="favicon" label="Favicon Image" type="file" wireModel="favicon" />

            <x-form.select name="plan_id" label="Select Plan" :options="$plans" wireModel="plan_id"
                placeholder="-- Select Plan --" />

            <div class="flex flex-row text-center  space-x-3">
                <x-form.button type="submit" title="Save" wireTarget="submit" />
                <x-form.button title="Back" class="bg-gray-500 hover:bg-gray-600 text-white"
                    route="superadmin.admin.index" />
            </div>
        </form>
    </div>
</div>
