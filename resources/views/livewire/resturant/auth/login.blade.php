<div class="w-full flex items-center justify-center px-4 sm:px-6 lg:px-8 pt-16"
    style="background-image: url('{{ asset('image/login3.png') }}'); background-size: cover; background-position: center; background-repeat: no-repeat; background-attachment: fixed; min-height: calc(100vh - 1rem);">


    <div class="w-full max-w-md">
        <div class="bg-white/90 backdrop-blur-md text-gray-800 rounded-lg shadow-lg px-4 sm:px-6 py-6 sm:py-8">
            <form class="space-y-6" wire:submit.prevent="submit">

                <div class="text-center">
                    {{-- <img class="mx-auto h-12 w-auto sm:h-14" src="{{ asset('icon/Jobhubwale_Final_01.png') }}" alt="Logo"> --}}
                    <h2 class="mt-4 text-xl sm:text-2xl font-bold text-gray-900">Sign in to your account</h2>
                    <x-form.error />
                </div>

                <x-form.input name="login" label="Email or Mobile" type="text"
                    placeholder="Enter your email or mobile" required wireModel="login" autocomplete="username" />

                <x-form.input name="password" label="Password" type="password" wireModel="password"
                    placeholder="Enter your password" required autocomplete="current-password" showToggle="true" />


                <div class="flex flex-row sm:flex-row items-center justify-between gap-3 sm:gap-0">
                    <x-form.input name="remember" label="Remember me" type="checkbox"
                        inputClass="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                        wrapperClass="flex items-center mt-4 gap-2" wireModel="remember_me" />

                    <a href="{{ route('password.request') }}" class="text-sm text-blue-600 hover:text-blue-500 font-medium">
                        Forgot your password?
                    </a>
                </div>

                <x-form.button type="submit"
                    class="w-full flex justify-center py-2 px-4 rounded-md shadow-sm text-sm sm:text-base font-medium text-white bg-gradient-to-r from-blue-500 to-cyan-400 hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200"
                    title="Sign in" wireTarget="submit" />

                <div class="text-center text-sm sm:text-base">
                    <p class="text-gray-600">
                        Don't have an account?
                        <a href="{{ route('register') }}" class="text-blue-600 hover:text-blue-500 font-medium ml-1">
                            Sign up
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>
