<div class="max-w-6xl mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-6">Restaurant Registration</h2>
    <x-form.error />

    <form wire:submit.prevent="register" class="space-y-8">

      <div class="rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b">Personal Profile</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <x-form.input name="name" label="Name" type="text" wireModel="name" required />
          <x-form.input name="email" label="Email" type="email" wireModel="email" readonly/>
          <x-form.input name="username" label="User Name" type="text" wireModel="username" required />
          <x-form.input name="mobile" label="Mobile" type="tel" wireModel="mobile" maxlength="10"
            oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)" readonly />
          <x-form.input name="password" label="Password" type="password" wireModel="password" showToggle="true" />
          <x-form.input name="password_confirmation" label="Confirm Password" type="password" wireModel="password_confirmation" showToggle="true" />
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
        <h3 class="border-b text-lg font-semibold text-gray-700 mb-4">Restaurant Profile</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <x-form.input name="restaurant_name" label="Restaurant Name" type="text" wireModel="restaurant_name" required />
          <x-form.input name="restaurant_email" label="Restaurant Email" type="email" wireModel="restaurant_email" />
          <x-form.input name="resto_mobile" label="Restaurant Mobile" type="tel" wireModel="resto_mobile" maxlength="10"
            oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)" />
            <x-form.input name="gst" label="GST No" type="text" wireModel="gst" />
            <x-form.input name="fssai" label="FSSAI Number" type="text" wireModel="fssai" required />
          <x-form.input name="resto_pincode" label="Restaurant Pincode" type="text" wireModelLive="resto_pincode" required />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mt-4">
            <x-form.input name="resto_country_name" label="Country" type="text" wireModel="resto_country_name" readonly />
            <x-form.input name="resto_state_name" label="State" type="text" wireModel="resto_state_name" readonly />
            <x-form.input name="resto_city_name" label="City" type="text" wireModel="resto_city_name" readonly />
            <x-form.input name="resto_district_name" label="District" type="text" wireModel="resto_district_name" readonly />
        </div>
        <x-form.input name="address" label="Restaurant Address" type="textarea" wireModel="address" class="md:col-span-2" />
      </div>

      <div class="rounded-lg p-6">
        <h3 class="border-b text-lg font-semibold text-gray-700 mb-4">Bank Details</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <x-form.input name="bank_name" label="Bank Name" wireModel="bank_name" />
          <x-form.select name="account_type" label="Account Type"
            :options="['savings' => 'Savings', 'current' => 'Current']"
            placeholder="-- Select Type --" wireModel="account_type" />
          <x-form.input name="holder_name" label="Holder Name" wireModel="holder_name" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
          <x-form.input name="account_number" label="Account Number" wireModel="account_number" />
          <x-form.input name="ifsc" label="IFSC" wireModel="ifsc" />
          <x-form.input name="upi_id" label="UPI ID" wireModel="upi_id" />
        </div>
      </div>

      <div class="rounded-lg p-6">
        <h3 class="border-b text-lg font-semibold text-gray-700 mb-4">Meta Settings</h3>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <x-form.input name="meta_title" label="Meta Title" type="text" wireModel="meta_title" />
          <x-form.input name="meta_keywords" label="Meta Keywords" type="text" wireModel="meta_keywords" />
        </div>

        <div class="mt-4">
          <x-form.input name="meta_description" label="Meta Description" type="textarea" wireModel="meta_description" />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4 items-end">
          <x-form.input name="favicon" label="Favicon Image" type="file" wireModel="favicon" />
          @if ($oldFavicon)
            <div class="md:col-span-2">
              <p class="text-sm mb-2">Current Favicon:</p>
              <img src="{{ asset('storage/' . $oldFavicon) }}" class="w-16 h-16 rounded" />
            </div>
          @endif
        </div>
      </div>

      <div class="flex justify-start gap-4">
        <x-form.button type="submit" title="Register" wireTarget="register" />
        <x-form.button title="Back" class="bg-gray-500 hover:bg-gray-600 text-white" route="login" />
      </div>
    </form>
  </div>
