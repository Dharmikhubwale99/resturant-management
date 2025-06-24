<nav class="bg-white shadow-lg sticky top-0 z-50" x-data="{ mobileMenuOpen: false, profileMenuOpen: false }">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <div class="flex items-center">
                <a href="#" class="text-xl font-bold text-gray-800">
                    <img src="{{ asset('icon/Jobhubwale_Final_01.png') }}" alt="Logo" class="h-10 w-auto">
                </a>
            </div>

            <div class="hidden md:flex space-x-8 items-center">

                    <a href="{{ route('superadmin.plans.index') }}" class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200">
                        Plan

                    </a>

                    <a href="{{ route('superadmin.settings') }}" class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200">
                        Setting
                    </a>

                    <a href="#" class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200">
                        Leads
                    </a>

            </div>

            <div class="flex items-center space-x-4">
                <a href="#" class="hidden md:block text-gray-600 hover:text-blue-600 transition-colors duration-200">
                    <i class="fi fi-rr-sign-out-alt text-xl"></i>
                </a>

                <div class="relative">
                    <button @click="profileMenuOpen = !profileMenuOpen" class="flex items-center space-x-2 focus:outline-none">
                        <img src="{{ asset('image/Admin.png') }}" alt="Company Profile"
                            class="w-10 h-10 rounded-full object-cover border-2 border-gray-300 hover:border-blue-500 transition-colors duration-200" />
                    </button>

                    <div x-show="profileMenuOpen" @click.away="profileMenuOpen = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                        <div class="px-4 py-3 border-b">
                            {{-- <p class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->personal_name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p> --}}
                        </div>

                        <div class="block md:hidden ">

                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Admins</a>

                                <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Users</a>

                                <a href="{{ route('superadmin.settings') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Settings</a>

                        </div>

                        <div class="py-1">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">View Profile</a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit Profile</a>

                            {{-- @if (auth()->check() && auth()->user()->refer_code)
                                <input type="text" id="referralLink"
                                    value="{{ route('register', ['ref' => auth()->user()->refer_code]) }}"
                                    class="hidden" readonly>
                                <button onclick="copyReferralLink()" class="block py-2 px-4 text-sm w-full text-left hover:bg-gray-100">Copy Referral Link</button>
                            @endif --}}

                            <a href="{{ route('logout')}}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Mobile menu icon (if needed)
            <div class="md:hidden">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-600 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path x-show="!mobileMenuOpen" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="mobileMenuOpen" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div> --}}
        </div>
    </div>
</nav>

@push('scripts')
    <script>
        function copyReferralLink() {
            navigator.clipboard.writeText(document.getElementById("referralLink").value)
                .then(() => alert("Referral link copied to clipboard!"))
                .catch(err => console.error("Failed to copy:", err));
        }
    </script>
@endpush
