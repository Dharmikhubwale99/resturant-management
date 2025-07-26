<div class="min-h-screen flex items-center justify-center bg-black p-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg overflow-hidden mx-2">
        <div class="p-4 sm:p-6 md:p-8">
            <div class="text-center mb-4">
                <img src="{{ asset('assets/images/logo.jpeg') }}" alt="L-FENSO CERAMIC" class="h-10 mx-auto mb-2">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Forgot Password</h2>
            </div>

            @if ($successMessage)
                <div class="mb-4 p-4 rounded-lg bg-green-100 border border-green-200 text-green-700">
                    {{ $successMessage }}
                </div>
            @endif

            @if ($otpSent)
                <form wire:submit.prevent="verifyOtpAndReset" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-1">Enter OTP</label>
                        <input wire:model="otp" type="text" maxlength="6"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                        @error('otp')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-1">New Password</label>
                        <input wire:model="new_password" type="password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                        @error('new_password')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-1">Confirm Password</label>
                        <input wire:model="new_password_confirmation" type="password"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg" required>
                    </div>
                    <x-form.button type="submit"
                        class="w-full flex justify-center py-2 px-4 rounded-md shadow-sm text-sm sm:text-base font-medium text-white bg-gradient-to-r from-blue-500 to-cyan-400 hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
                        title="Reset Password" wireTarget="resetPassword" />
                </form>
            @else
                <form wire:submit.prevent="sendResetLink" class="space-y-4">
                    <div>
                        <label class="block text-gray-700 text-sm font-medium mb-1">Email or WhatsApp Number</label>
                        <input wire:model="identifier" type="text"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:border-transparent text-sm bg-white text-gray-800"
                            placeholder="Enter your email or WhatsApp number" required />
                        @error('identifier')
                            <span class="text-red-500 text-xs">{{ $message }}</span>
                        @enderror
                    </div>
                    <x-form.button type="submit"
                        class="w-full flex justify-center py-2 px-4 rounded-md shadow-sm text-sm sm:text-base font-medium text-white bg-gradient-to-r from-blue-500 to-cyan-400 hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
                        title="Send Reset Link / OTP" wireTarget="sendResetLink" />

                    <div class="mt-3 text-center">
                        <a href="{{ route('login') }}"
                            class="text-sm text-blue-600 hover:text-blue-500 font-medium">Back to Login</a>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>
