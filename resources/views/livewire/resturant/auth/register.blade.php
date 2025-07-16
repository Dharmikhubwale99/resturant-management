<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-md">
        <h2 class="text-xl font-semibold text-center mb-4">Register</h2>
        <x-form.error />
        <form wire:submit.prevent="register" class="space-y-4">

            <x-form.input name="name" label="Personal Name" required wireModel="name" />

            <x-form.input name="email" label="PersonalEmail" type="email" required wireModel="email" />

            <x-form.input name="mobile" label="Personal Mobile" required wireModel="mobile" />

            <x-form.input name="password" label="Password" type="password" required wireModel="password"
                autocomplete="current-password" showToggle="true" />

            <x-form.input name="confirm_password" label="Confirm Password" type="password" required
                wireModel="confirm_password" autocomplete="current-password" showToggle="true"/>

            <x-form.button title="Register" type="submit" class="w-full bg-indigo-600 text-white" />

        </form>
        @if ($showOtpForm)
            <div
                class="fixed inset-0 bg-transparent bg-opacity-40 backdrop-blur-sm flex items-center justify-center z-50">
                <div class="bg-white p-6 rounded-lg shadow-xl w-100 max-w-sm">
                    <h2 class="text-xl font-semibold text-center mb-4">Enter OTP</h2>

                    <form wire:submit.prevent="verifyOtp" class="space-y-4">
                        <x-form.input name="otp" label="OTP Code" placeholder="Enter 6-digit OTP" type="text"
                            required wireModel="otp" />
                        <div class="flex justify-between items-center">
                            <x-form.button title="Verify OTP" type="submit"
                                class="bg-green-600 hover:bg-green-700 text-white" wireTarget="verifyOtp" />
                            <x-form.button title="Resend OTP" type="button"
                                class="bg-gray-600 hover:bg-gray-700 text-white" wireClick="resendOtp"
                                wireTarget="resendOtp" />
                        </div>
                    </form>
                </div>
            </div>
        @endif

    </div>
</div>
