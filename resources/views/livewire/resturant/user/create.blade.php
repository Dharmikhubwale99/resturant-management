<div class="p-6 bg-white rounded shadow max-w-4xl mx-auto">
    <h2 class="text-2xl font-bold mb-6">Add User</h2>
    <x-form.error />
    <form wire:submit.prevent="submit" class="space-y-4">
        <x-form.input name="name" label="Full Name" wireModel="name" required placeholder="Enter full name" />

        <x-form.input name="userename" label="User Name" wireModel="userename" required placeholder="Enter user name" />

        <x-form.input name="email" label="Email" type="email" wireModel="email" required
        placeholder="Enter email address" />

        <x-form.input name="mobile" label="Mobile Number" type="text" wireModel="mobile" required
            placeholder="Enter 10-digit mobile number" />

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

        <x-form.input name="password" label="Password" type="password" wireModel="password" required
            placeholder="Create a password" showToggle="true"/>

        <x-form.input name="password_confirmation" label="Confirm Password" type="password"
            wireModel="password_confirmation" required placeholder="Confirm the password" showToggle="true"/>

        <div class="flex flex-row text-center  space-x-3">
                <x-form.button type="submit" title="Save" wireTarget="submit" />
                <x-form.button title="Back" class="bg-gray-500 hover:bg-gray-600 text-white"
                    route="restaurant.users.index" />
            </div>
    </form>
</div>
