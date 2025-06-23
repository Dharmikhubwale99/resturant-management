<div class="min-h-screen flex items-center justify-center bg-black p-4">
    <div class="w-full max-w-md bg-white rounded-lg shadow-lg overflow-hidden mx-2">
        <div class="p-4 sm:p-6 md:p-8">
            <!-- Logo Section -->
            <div class="text-center mb-4">
                <img src="{{ asset('assets/images/logo.jpeg') }}" alt="L-FENSO CERAMIC" class="h-10 mx-auto mb-2">
                <h2 class="text-xl sm:text-2xl font-bold text-gray-800">Login to Your Account</h2>
            </div>

            @if (session('success'))
                <div class="mb-4 p-4 rounded-lg bg-green-100 border border-green-200">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <p class="text-green-700">{{ session('success') }}</p>
                    </div>
                </div>
            @endif

            <form wire:submit.prevent="submit" class="space-y-3">
                {{-- <x-wireui:errors only="admin" /> --}}

                <div>
                    <label for="mobile_number" class="block text-gray-700 text-sm font-medium mb-1 sm:mb-2">WhatsApp Number</label>
                    <input wire:model="mobile_number" type="text" id="mobile_number"
                        class="w-full px-3 py-2 sm:px-4 sm:py-2 border border-gray-300 rounded-lg focus:border-transparent text-sm sm:text-base bg-white text-gray-800"
                        placeholder="Enter your WhatsApp number" required />
                    @error('mobile_number')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-gray-700 text-sm font-medium mb-1 sm:mb-2">Password</label>
                    <input wire:model="password" type="password" id="password"
                        class="w-full px-3 py-2 sm:px-4 sm:py-2 border border-gray-300 rounded-lg focus:border-transparent text-sm sm:text-base bg-white text-gray-800"
                        placeholder="Enter your password" required />
                    @error('password')
                        <span class="text-red-500 text-xs">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex flex-wrap items-center justify-between gap-y-2">
                    <label class="flex items-center">
                        <input type="checkbox" wire:model="remember_me" class="form-checkbox h-4 w-4 text-[#9CD118]">
                        <span class="ml-2 text-sm text-gray-700">Remember me</span>
                    </label>
                    {{-- <a href="{{ route('password.request') }}" class="text-xs text-[#9CD118] hover:underline">
                        Forgot Password? (Email/WhatsApp)
                    </a> --}}
                </div>

                <button type="submit"
                    class="w-full bg-[#9CD118] hover:bg-[#8BBB0C] text-black font-semibold py-2 px-4 rounded-lg transition duration-200 relative text-sm">
                    <span wire:loading.remove>Login</span>
                    <span wire:loading class="flex items-center justify-center">
                        <svg class="animate-spin w-4 h-4 mr-2" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4" fill="none"></circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                    </span>
                </button>

                <div class="mt-3 sm:mt-4 text-center">
                    <p class="text-gray-600 text-sm">
                        Don't have an account?
                        {{-- <a href="{{ route('register') }}" class="text-[#9CD118] hover:text-[#8BBB0C] font-medium">Register here</a> --}}
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
