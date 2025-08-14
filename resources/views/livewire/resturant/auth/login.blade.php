<div class="relative w-full flex items-center justify-center px-4 sm:px-6 lg:px-8 pt-16 min-h-[calc(100vh-1rem)]"
     style="background-image:url('{{ asset('image/resturant.jpg') }}'); background-size:cover; background-position:center; background-repeat:no-repeat; background-attachment:fixed;">
    <div class="absolute inset-0 bg-black/30"></div>

    <div class="relative w-full max-w-md">
        <div class="bg-[#EAE7E1]/90 backdrop-blur-md text-gray-800 rounded-2xl shadow-xl ring-1 ring-black/5 px-4 sm:px-6 py-6 sm:py-8">
            <form class="space-y-6" wire:submit.prevent="submit">
                <div class="text-center">
                    <h2 class="mt-1 text-2xl font-bold text-gray-900">Sign in to your account</h2>
                    <x-form.error />
                </div>

                <x-form.input name="login" label="Email or Mobile" type="text"
                    placeholder="Enter your email or mobile" required wireModel="login" autocomplete="username"
                    inputClass="focus:ring-[#C9894B] focus:border-[#C9894B]" />

                <x-form.input name="password" label="Password" type="password" wireModel="password"
                    placeholder="Enter your password" required autocomplete="current-password" showToggle="true"
                    inputClass="focus:ring-[#C9894B] focus:border-[#C9894B]" />

                <div class="flex items-center justify-between gap-3">
                    <x-form.input name="remember" label="Remember me" type="checkbox"
                        inputClass="h-4 w-4 text-amber-600 focus:ring-amber-600 border-gray-300 rounded"
                        wrapperClass="flex items-center mt-4 gap-2" wireModel="remember_me" />

                    <a href="{{ route('password.request') }}"
                       class="text-sm font-medium text-[#B86A2E] hover:text-[#8A5E3B]">
                        Forgot your password?
                    </a>
                </div>

                <x-form.button type="submit"
                    class="w-full flex justify-center py-2.5 px-4 rounded-lg shadow-sm text-sm sm:text-base font-semibold text-white
                           bg-gradient-to-r from-[#C9894B] to-[#8A5E3B] hover:opacity-95 focus:outline-none
                           focus:ring-2 focus:ring-[#C9894B] focus:ring-offset-2 transition-all duration-200"
                    title="Sign in" wireTarget="submit" />

                <div class="text-center text-sm sm:text-base">
                    <p class="text-gray-700">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="ml-1 font-medium text-[#B86A2E] hover:text-[#8A5E3B]">
                            Sign up
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
