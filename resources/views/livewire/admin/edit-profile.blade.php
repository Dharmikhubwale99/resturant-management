<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="p-6 bg-white rounded shadow max-w-3xl w-full">
        <x-form.error />

        <h2 class="text-2xl font-bold mb-6 text-center">Edit Admin Profile</h2>

        <x-form.input name="name" label="Name" wireModel="name" required />
        <x-form.input name="email" label="Email" type="email" wireModel="email" required />
        <x-form.input name="username" label="User Name" wireModel="username" required />
        <x-form.input name="mobile" label="Mobile" wireModel="mobile" required />
        <x-form.input name="password" label="Password" type="password" wireModel="password" showToggle="true" />
        <x-form.input name="confirm_password" label="Confirm Password" type="password" wireModel="confirm_password"
            showToggle="true" />

        <div class="flex flex-row text-center  space-x-3">
            <x-form.button title="Back" class="bg-gray-500 hover:bg-gray-600 text-white"
            route="superadmin.admin.index" />
            <x-form.button title="Update Profile" wireClick="updateProfile" wireTarget="updateProfile"/>
        </div>
    </div>
</div>
