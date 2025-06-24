<div class="p-6 max-w-3xl mx-auto bg-white shadow rounded">
    <h2 class="text-2xl font-bold mb-6">Create Restaurant & User</h2>

    @if (session()->has('message'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
            {{ session('message') }}
        </div>
    @endif

    <form wire:submit.prevent="submit" class="space-y-5">
        <h3 class="text-lg font-semibold text-gray-700">User Information</h3>

        <x-form.input name="user_name" label="Name" type="text" wireModel="user_name" required />

        <x-form.input name="email" label="Email" type="email" wireModel="email" required />

        <x-form.input name="mobile" label="Mobile" type="text" wireModel="mobile" required />

        <x-form.input name="password" label="Password" type="password" wireModel="password" required />

        <x-form.input name="password_confirmation" label="Confirm Password" type="password"
            wireModel="password_confirmation" required />

        <x-form.input name="gst_number" label="GST Number" type="text" wireModel="gst_no" />

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

        <x-form.input name="restaurant_address" label="Address" type="textarea" wireModel="restaurant_address" />

        <div class="flex items-center justify-between">
            <x-form.button type="submit" title="Save" wireTarget="submit" />
            <x-form.button title="Back" class="bg-gray-500 hover:bg-gray-600 text-white"
                route="superadmin.admin.index" />
        </div>
    </form>
</div>
