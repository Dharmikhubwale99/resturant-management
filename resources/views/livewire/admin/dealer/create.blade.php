<div class="max-w-6xl mx-auto px-4 py-8">
    <h2 class="text-2xl font-bold mb-6">Create Dealer</h2>
    <x-form.error />

    <form wire:submit.prevent="submit" class="space-y-8">

        <div class="rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 border-b">User Information</h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <x-form.input name="name" label="Name" type="text" wireModel="name" required />
                <x-form.input name="email" label="Email" type="email" wireModel="email" required />
                <x-form.input name="username" label="User Name" type="text" wireModel="username" required />
                <x-form.input name="mobile" label="Mobile" type="tel" wireModel="mobile" maxlength="10"
                    oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)" required />
                <x-form.input name="password" label="Password" type="password" wireModel="password" required showToggle="true" />
                <x-form.input name="password_confirmation" label="Confirm Password" type="password"
                    wireModel="password_confirmation" required showToggle="true" />
                </div>

                <div class="mb-4">
                    <x-form.select name="role" label="Role" :options="$data['roles']" wireModel="role" required
                        placeholder="Select Role" />
                    @error('role')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <x-form.checkbox-group name="permissions" :groups="$data['permissions']" wireModel="permissions" />
                </div>

        </div>

        <div class="flex justify-start gap-4">
            <x-form.button type="submit" title="Save" wireTarget="submit" />
            <x-form.button title="Back" class="bg-gray-500 hover:bg-gray-600 text-white"
                route="superadmin.admin.index" />
        </div>
    </form>
</div>
