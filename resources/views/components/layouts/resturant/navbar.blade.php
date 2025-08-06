<nav class="bg-white shadow-lg sticky top-0 z-50" x-data="{
    mobileMenuOpen: false,
    profileMenuOpen: false,
    start: 0,
    visible: 5,
    menuLinks: [
        @if (setting('moneyIn'))
            { text: 'Money In', href: '{{ route('restaurant.money-maintain') }}' },
        @endif
        @if(setting('moneyOut'))
            { text: 'Money Out', href: '{{ route('restaurant.money-out') }}' },
        @endif
        @if (setting('party'))
            { text: 'Party', href: '{{ route('restaurant.party') }}' },
        @endif
        @if (setting('user'))
            @can('user-index')
                { text: 'User', href: '{{ route('restaurant.users.index') }}' },
            @endcan
        @endif
        @if (setting('category_module'))
            @can('category-index')
                { text: 'Category', href: '{{ route('restaurant.categories.index') }}' },
            @endcan
        @endif
        @if (setting('item'))
            @can('item-index')
                { text: 'Item', href: '{{ route('restaurant.items.index') }}' },
            @endcan
        @endif
        @if (setting('area_module'))
            @can('area-index')
                { text: 'Area', href: '{{ route('restaurant.areas.index') }}' },
            @endcan
        @endif
        @if (setting('table'))
            @can('table-index')
                { text: 'Table', href: '{{ route('restaurant.tables.index') }}' },
            @endcan
        @endif
        @if (setting('expensetype'))
            @can('expensetype-index')
                { text: 'Expense-Type', href: '{{ route('restaurant.expense-types.index') }}' },
            @endcan
        @endif
        @if (setting('expenses'))
            @can('expenses-index')
                { text: 'Expenses', href: '{{ route('restaurant.expenses.index') }}' },
            @endcan
        @endif
        @if (setting('discount'))
            @can('discount-index')
                { text: 'Discount', href: '{{ route('restaurant.discount.index') }}' },
            @endcan
        @endif
        @if (setting('kitchen'))
            @can('kitchen-dashboard')
                { text: 'Kitchen', href: '{{ route('restaurant.kitchen.index') }}' },
            @endcan
        @endif
        @if ('order')
            @can('order')
                { text: 'Order', href: '{{ route('restaurant.waiter.dashboard') }}' },
            @endcan
        @endif
        @if (setting('report'))
            { text: 'Report', href: '{{ route('restaurant.report') }}' },
        @endif
    ]
}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('restaurant.dashboard') }}" class="text-xl font-bold text-gray-800">
                    <img src="{{ asset('storage/' . ($siteSettings->favicon ?? 'icon/hubwalelogopng.png')) }}" alt="Logo" class="h-10 w-auto">
                </a>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center w-full max-w-5xl relative overflow-hidden">
                <div class="flex items-center justify-between w-full">
                    <!-- Prev Button -->
                    <button @click="if(start > 0) start--"
                        class="text-lg px-3 py-2 bg-gray-200 rounded hover:bg-gray-300 flex-shrink-0">
                        &lt;
                    </button>

                    <!-- Centered Nav Links -->
                    <div class="flex space-x-6 justify-center items-center flex-1">
                        <template x-for="(link, index) in menuLinks.slice(start, start + visible)"
                            :key="index">
                            <a :href="link.href" x-text="link.text"
                                class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200 whitespace-nowrap"></a>
                        </template>
                    </div>

                    <!-- Next Button -->
                    <button @click="if(start + visible < menuLinks.length) start++"
                        class="text-lg px-3 py-2 bg-gray-200 rounded hover:bg-gray-300 flex-shrink-0">
                        &gt;
                    </button>
                </div>
            </div>


            <!-- Right Profile & Logout -->
            <div class="flex items-center space-x-4">
                <!-- Logout Icon (Desktop Only) -->
                <a href="{{ route('logout') }}"
                    class="hidden md:block text-gray-600 hover:text-blue-600 transition-colors duration-200">
                    <i class="fi fi-rr-sign-out-alt text-xl"></i>
                </a>

                <!-- Profile Dropdown -->
                <div class="relative">
                    <button @click="profileMenuOpen = !profileMenuOpen"
                        class="flex items-center space-x-2 focus:outline-none">
                        <img src="{{ asset('image/Admin.png') }}" alt="Company Profile"
                            class="w-10 h-10 rounded-full object-cover border-2 border-gray-300 hover:border-blue-500 transition-colors duration-200" />
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="profileMenuOpen" @click.away="profileMenuOpen = false"
                        x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-56 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none z-50">
                        <div class="px-4 py-3 border-b">
                            <p class="text-sm font-semibold text-gray-900 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                        </div>

                        <!-- Mobile Menu in Dropdown -->
                        <div class="block md:hidden">
                            <template x-for="(link, index) in menuLinks" :key="index">
                                <a :href="link.href" x-text="link.text"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"></a>
                            </template>
                        </div>

                        <!-- Logout -->
                        <div class="py-1">
                            <a href="{{ route('restaurant.edit-profile') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Edit Profile</a>
                            <a href="{{ route('logout') }}"
                                class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Optional: Mobile Toggle Button (hidden for now) --}}
            {{--
            <div class="md:hidden">
                <button @click="mobileMenuOpen = !mobileMenuOpen" class="text-gray-600 focus:outline-none">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                         viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path x-show="!mobileMenuOpen" d="M4 6h16M4 12h16M4 18h16" />
                        <path x-show="mobileMenuOpen" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            --}}
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
