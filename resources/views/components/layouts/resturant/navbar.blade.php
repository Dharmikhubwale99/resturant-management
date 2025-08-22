<nav class="bg-white shadow-lg sticky top-0 z-50" x-data="{
    mobileMenuOpen: false,
    profileMenuOpen: false,
    start: 0,
    visible: 5,
    openDropdown: null,
    menuLinks: [
        @if ('order') @can('order')
                { text: 'Order', href: '{{ route('restaurant.waiter.dashboard') }}' },
                @endcan @endif

        @if (setting('kitchen')) @can('kitchen-dashboard')
                { text: 'Kitchen', href: '{{ route('restaurant.kitchen.index') }}' },
            @endcan @endif

        @if (setting('user')) @can('user-index')
                { text: 'User', href: '{{ route('restaurant.users.index') }}' },
            @endcan @endif

        @if (setting('party')) @can('party-index')
                { text: 'Party', href: '{{ route('restaurant.party') }}' },
            @endcan @endif

        @if (setting('moneyOut')) @can('moneyout-index')
                { text: 'Money Out', href: '{{ route('restaurant.money-out') }}' },
            @endcan @endif

        @if (setting('moneyIn')) @can('moneyin-index')
                { text: 'Money In', href: '{{ route('restaurant.money-maintain') }}' },
            @endcan @endif

        @if (setting('expenses')) @can('expenses-index')
                { text: 'Expenses', href: '{{ route('restaurant.expenses.index') }}' },
            @endcan @endif

        @if (setting('item')) @can('item-index')
                { text: 'Item', href: '{{ route('restaurant.items.index') }}' },
            @endcan @endif

        @if (setting('discount')) @can('discount-index')
                { text: 'Discount', href: '{{ route('restaurant.discount.index') }}' },
            @endcan @endif

        @if (setting('table')) @can('table-index')
                { text: 'Table', href: '{{ route('restaurant.tables.index') }}' },
            @endcan @endif

        @if (setting('category_module')) @can('category-index')
                { text: 'Category', href: '{{ route('restaurant.categories.index') }}' },
            @endcan @endif

        @if (setting('area_module')) @can('area-index')
                { text: 'Area', href: '{{ route('restaurant.areas.index') }}' },
            @endcan @endif

        @if (setting('expensetype')) @can('expensetype-index')
                { text: 'Expense-Type', href: '{{ route('restaurant.expense-types.index') }}' },
            @endcan @endif

        { text: 'File Manager', href: '{{ url('restaurant/file-manager') }}?type=image', newTab: true },


        @if (setting('report')) @can('report-index')
                { text: 'Report', href: '{{ route('restaurant.report') }}' },
            @endcan @endif

        {
            text: 'Setting',
            children: [
                { text: 'Print Setting', href: '{{ route('restaurant.bill-print-settings') }}' },
                {{-- { text: 'Meta Setting', href: '{{ route('restaurant.edit-profile') }}' } --}}
            ]
        },
    ]
}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('restaurant.dashboard') }}" class="text-xl font-bold text-gray-800">
                    <img src="{{ asset('storage/' . ($siteSettings->favicon ?? 'icon/hubwalelogopng.png')) }}"
                        alt="Logo" class="h-10 w-auto">
                </a>

            </div>
            <div class="flex md:hidden items-center justify-between ml-3 gap-6">
                <a href="{{ route('restaurant.waiter.dashboard') }}" class="text-gray-700 hover:text-blue-600">
                    <i class="fas fa-shopping-cart text-lg"></i>
                </a>
                <a href="{{ route('restaurant.party') }}" class="text-gray-700 hover:text-blue-600">
                    <i class="fas fa-utensils text-lg"></i>
                </a>
                <a href="{{ route('restaurant.party') }}" class="text-gray-700 hover:text-blue-600">
                    <i class="fas fa-user text-lg"></i>
                </a>
            </div>

            <!-- Desktop Menu -->
            <div class="hidden md:flex items-center w-full max-w-5xl relative">
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
                            <div class="relative">
                                <!-- Simple link -->
                                <template x-if="!link.children">
                                    <a :href="link.href" :target="link.newTab ? '_blank' : null" x-text="link.text"
                                        class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200 whitespace-nowrap">
                                    </a>
                                </template>

                                <!-- Dropdown parent -->
                                <template x-if="link.children">
                                    <div class="relative" @click.outside="openDropdown = null">
                                        <button @click="openDropdown = openDropdown === index ? null : index"
                                            class="text-gray-700 hover:text-blue-600 font-medium transition-colors duration-200 whitespace-nowrap inline-flex items-center gap-1"
                                            type="button">
                                            <span x-text="link.text"></span>
                                            <svg class="w-4 h-4 transition-transform"
                                                :class="{ 'rotate-180': openDropdown === index }" viewBox="0 0 20 20"
                                                fill="currentColor">
                                                <path fill-rule="evenodd"
                                                    d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.24a.75.75 0 01-1.06 0L5.21 8.29a.75.75 0 01.02-1.08z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </button>

                                        <!-- Dropdown menu -->
                                        <div x-show="openDropdown === index" x-transition
                                        class="absolute left-1/2 -translate-x-1/2 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black/5 z-[60]">
                                            <template x-for="child in link.children" :key="child.text">
                                                <a :href="child.href" @click="openDropdown = null"
                                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100"
                                                    x-text="child.text"></a>
                                            </template>
                                        </div>
                                    </div>
                                </template>
                            </div>
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
                    {{-- <div class="flex justify-start items-center space-x-6">
                        <a href="{{ route('orders.index') }}" class="text-gray-700 hover:text-blue-600">
                            <i class="fas fa-shopping-cart text-lg"></i>
                        </a>
                        <a href="{{ route('users.index') }}" class="text-gray-700 hover:text-blue-600">
                            <i class="fas fa-user text-lg"></i>
                        </a>
                    </div> --}}

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
                        <div class="block md:hidden  max-h-60 overflow-y-auto">
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
