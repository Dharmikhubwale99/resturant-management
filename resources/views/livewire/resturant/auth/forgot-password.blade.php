<div class="relative w-full flex items-center justify-center px-4 sm:px-6 lg:px-8 pt-16 min-h-[calc(100vh-1rem)]"
     style="background-image:url('{{ asset('image/resturant.jpg') }}'); background-size:cover; background-position:center; background-repeat:no-repeat; background-attachment:fixed;">

    <!-- Black Overlay -->
    <div class="absolute inset-0 bg-black/30"></div>

    <div class="relative w-full max-w-md">
        <div class="bg-[#EAE7E1]/90 backdrop-blur-md text-gray-800 rounded-2xl shadow-xl ring-1 ring-black/5 px-4 sm:px-6 py-6 sm:py-8">

            <!-- Logo & Title -->
            <div class="text-center mb-4">
                <img src="{{ asset('assets/images/hubwalelogopng.png') }}" alt="L-FENSO CERAMIC" class="h-12 mx-auto mb-2">
                <h2 class="text-2xl font-bold text-gray-900">Forgot Password</h2>
            </div>

            <!-- Success Message -->
            @if ($successMessage)
                <div class="mb-4 p-4 rounded-lg bg-green-100 border border-green-200 text-green-700">
                    {{ $successMessage }}
                </div>
            @endif

            <!-- OTP Form -->
            @if ($otpSent)
                <form wire:submit.prevent="verifyOtpAndReset" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-1">Enter OTP</label>
                        <input wire:model="otp" type="text" maxlength="6"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-[#C9894B] focus:border-[#C9894B]" required>
                        @error('otp')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>

                    <x-form.input name="new_password" label="New Password" type="password"
                        placeholder="Enter your new password" required wireModel="new_password"
                        autocomplete="new-password" showToggle="true"
                        inputClass="focus:ring-[#C9894B] focus:border-[#C9894B]" />

                    <x-form.input name="new_password_confirmation" label="Confirm Password" type="password"
                        placeholder="Confirm your new password" required wireModel="new_password_confirmation"
                        autocomplete="new-password" showToggle="true"
                        inputClass="focus:ring-[#C9894B] focus:border-[#C9894B]" />

                    <x-form.button type="submit"
                        class="w-full flex justify-center py-2.5 px-4 rounded-lg shadow-sm text-sm sm:text-base font-semibold text-white
                               bg-gradient-to-r from-[#C9894B] to-[#8A5E3B] hover:opacity-95 focus:outline-none
                               focus:ring-2 focus:ring-[#C9894B] focus:ring-offset-2 transition-all duration-200"
                        title="Reset Password" wireTarget="resetPassword" />
                </form>
            @else
                <!-- Send OTP Form -->
                <form wire:submit.prevent="sendResetLink" class="space-y-4">
                    <x-form.input name="identifier" label="Email or WhatsApp Number" type="text"
                        placeholder="Enter your email or mobile" required wireModel="identifier"
                        autocomplete="username"
                        inputClass="focus:ring-[#C9894B] focus:border-[#C9894B]" />

                    <x-form.button type="submit"
                        class="w-full flex justify-center py-2.5 px-4 rounded-lg shadow-sm text-sm sm:text-base font-semibold text-white
                               bg-gradient-to-r from-[#C9894B] to-[#8A5E3B] hover:opacity-95 focus:outline-none
                               focus:ring-2 focus:ring-[#C9894B] focus:ring-offset-2 transition-all duration-200"
                        title="Send Reset Link / OTP" wireTarget="sendResetLink" />

                    <div class="mt-3 text-center">
                        <a href="{{ route('login') }}" class="text-sm font-medium text-[#B86A2E] hover:text-[#8A5E3B]">
                            Back to Login
                        </a>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
