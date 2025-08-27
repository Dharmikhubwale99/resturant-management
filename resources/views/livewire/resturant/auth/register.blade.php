<div class="relative w-full flex items-center justify-center px-4 sm:px-6 lg:px-8 pt-16 min-h-[calc(100vh-1rem)]"
     style="background-image:url('{{ asset('image/resturant.jpg') }}'); background-size:cover; background-position:center; background-repeat:no-repeat; background-attachment:fixed;">

    <!-- Black Overlay -->
    <div class="absolute inset-0 bg-black/30"></div>

    <div class="relative w-full max-w-md">
        <div class="bg-[#EAE7E1]/90 backdrop-blur-md text-gray-800 rounded-2xl shadow-xl ring-1 ring-black/5 px-4 sm:px-6 py-6 sm:py-8">

            <h2 class="text-2xl font-bold text-center mb-4 text-gray-900">Register</h2>
            <x-form.error />

            <form wire:submit.prevent="register" class="space-y-4">
                <x-form.input name="name" label="Personal Name" required wireModel="name"
                    inputClass="focus:ring-[#C9894B] focus:border-[#C9894B]" />

                <x-form.input name="username" label="User Name" required wireModel="username"
                    inputClass="focus:ring-[#C9894B] focus:border-[#C9894B]" />

                <x-form.input name="email" label="Personal Email" type="email" required wireModel="email"
                    inputClass="focus:ring-[#C9894B] focus:border-[#C9894B]" />

                <x-form.input name="mobile" label="Personal Mobile" required wireModel="mobile" maxlength="10"
                oninput="this.value=this.value.replace(/[^0-9]/g,'').slice(0,10)" inputClass="focus:ring-[#C9894B] focus:border-[#C9894B]" />

                <x-form.input name="password" label="Password" type="password" required wireModel="password"
                    autocomplete="current-password" showToggle="true"
                    inputClass="focus:ring-[#C9894B] focus:border-[#C9894B]" />

                <x-form.input name="confirm_password" label="Confirm Password" type="password" required
                    wireModel="confirm_password" autocomplete="current-password" showToggle="true"
                    inputClass="focus:ring-[#C9894B] focus:border-[#C9894B]" />

                <x-form.button title="Register" type="submit" wireTarget="register"
                    class="w-full flex justify-center py-2.5 px-4 rounded-lg shadow-sm text-sm sm:text-base font-semibold text-white
                           bg-gradient-to-r from-[#C9894B] to-[#8A5E3B] hover:opacity-95 focus:outline-none
                           focus:ring-2 focus:ring-[#C9894B] focus:ring-offset-2 transition-all duration-200" />
            </form>

            @if ($showOtpForm)
                <div class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-center justify-center z-50">
                    <div class="bg-white p-6 rounded-lg shadow-xl w-100 max-w-sm">
                        <h2 class="text-xl font-semibold text-center mb-4">Enter OTP</h2>
                        <form wire:submit.prevent="verifyOtp" class="space-y-4">
                            <x-form.input name="otp" label="OTP Code" placeholder="Enter 6-digit OTP" type="text"
                                required wireModel="otp" inputClass="focus:ring-[#C9894B] focus:border-[#C9894B]" />
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

            <div class="text-center text-sm sm:text-base mt-4">
                <p class="text-gray-700">
                    Already have an account?
                    <a href="{{ route('login') }}" class="ml-1 font-medium text-[#B86A2E] hover:text-[#8A5E3B]">
                        Sign in
                    </a>
                </p>
            </div>
        </div>
    </div>
</div>
