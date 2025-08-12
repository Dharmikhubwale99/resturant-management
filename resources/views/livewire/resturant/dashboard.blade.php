<div>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    animation: {
                        'fade-in': 'fadeIn 0.5s ease-in-out',
                        'slide-up': 'slideUp 0.6s ease-out',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        fadeIn: {
                            '0%': {
                                opacity: '0'
                            },
                            '100%': {
                                opacity: '1'
                            },
                        },
                        slideUp: {
                            '0%': {
                                transform: 'translateY(20px)',
                                opacity: '0'
                            },
                            '100%': {
                                transform: 'translateY(0)',
                                opacity: '1'
                            },
                        }
                    }
                }
            }
        }
    </script>
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .gradient-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
        }

        .gradient-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }

        .gradient-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }

        .glass-effect {
            backdrop-filter: blur(16px);
            fg background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .card-shadow {
            box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        .card-hover-shadow {
            transition: all 0.3s ease;
        }

        .card-hover-shadow:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }
    </style>

    <div class="bg-gradient-to-br from-slate-900 via-pink-900 to-slate-900 min-h-screen p-4">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-8 animate-fade-in">
                <h1 class="text-4xl font-bold text-white mb-2">Dashboard Overview</h1>
                <p class="text-gray-300">Monitor your key metrics at a glance</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <a href="{{ route('restaurant.sales-report') }}" class="block">
                    <div class="card-hover-shadow bg-white rounded-xl p-6 card-shadow animate-slide-up">
                        <div class="flex items-center justify-between mb-4">
                            <div class="gradient-bg text-white p-3 rounded-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1">
                                    </path>
                                </svg>
                            </div>
                            <span class="text-green-500 text-sm font-medium">{{ $todayIncomePercentage }}%</span>
                        </div>
                        <h3 class="text-gray-600 text-sm font-medium mb-1">Today's Sale</h3>
                        <p class="text-2xl font-bold text-gray-900">₹{{ number_format($todayIncome, 2) }}</p>
                        <div class="mt-4 bg-gray-100 rounded-full h-2">
                            <div class="gradient-bg h-2 rounded-full transition-all duration-1000"
                                style="width: {{ $todayIncomeProgress }}%;"></div>
                        </div>
                    </div>
                </a>


                <a href="{{ route('restaurant.payment-report') }}" class="block">
                    <div class="card-hover-shadow bg-white rounded-xl p-6 card-shadow animate-slide-up"
                        style="animation-delay: 0.1s;">
                        <div class="flex items-center justify-between mb-4">
                            <div class="gradient-success text-white p-3 rounded-lg">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <rect x="2" y="7" width="20" height="10" rx="2" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <circle cx="12" cy="12" r="3" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                    <path d="M6 10v4M18 10v4" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                            <span class="text-green-500 text-sm font-medium">{{ $todayMoneyPercentage }}%</span>
                        </div>
                        <h3 class="text-gray-600 text-sm font-medium mb-1">Today's Money</h3>
                        <p class="text-2xl font-bold text-gray-900">₹{{ number_format($todayMoney, 2) }}</p>
                        <div class="mt-4 bg-gray-100 rounded-full h-2">
                            <div class="gradient-success h-2 rounded-full transition-all duration-1000"
                                style="width: {{ $todayMoneyProgress }}%;"></div>
                        </div>
                    </div>
                </a>


                <div class="card-hover-shadow bg-white rounded-xl p-6 card-shadow animate-slide-up"
                    style="animation-delay: 0.2s;">
                    <div class="flex items-center justify-between mb-4">
                        <div class="gradient-warning text-white p-3 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
                            </svg>
                        </div>
                        <span class="text-red-500 text-sm font-medium">-2.1%</span>
                    </div>
                    <h3 class="text-gray-600 text-sm font-medium mb-1">Today's Orders</h3>
                    <p class="text-2xl font-bold text-gray-900">{{ $todayOrders }}</p>
                    <div class="mt-4 bg-gray-100 rounded-full h-2">
                        <div class="gradient-warning h-2 rounded-full w-2/3 transition-all duration-1000"></div>
                    </div>
                </div>

                <div class="card-hover-shadow bg-white rounded-xl p-6 card-shadow animate-slide-up"
                    style="animation-delay: 0.3s;">
                    <div class="flex items-center justify-between mb-4">
                        <div class="gradient-info text-white p-3 rounded-lg">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                                </path>
                            </svg>
                        </div>
                        <span class="text-green-500 text-sm font-medium">+5.7%</span>
                    </div>
                    <h3 class="text-gray-600 text-sm font-medium mb-1">Conversion Rate</h3>
                    <p class="text-2xl font-bold text-gray-900">3.24%</p>
                    <div class="mt-4 bg-gray-100 rounded-full h-2">
                        <div class="gradient-info h-2 rounded-full w-1/3 transition-all duration-1000"></div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                <div
                    class="card-hover-shadow bg-white rounded-xl p-6 card-shadow animate-slide-up col-span-1 lg:col-span-2 xl:col-span-1">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">Performance</h3>
                        <div class="flex space-x-2">
                            <div class="w-3 h-3 bg-green-500 rounded-full animate-pulse-slow"></div>
                            <span class="text-sm text-gray-500">Live</span>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">CPU Usage</span>
                            <span class="text-sm font-medium text-gray-900">45%</span>
                        </div>
                        <div class="bg-gray-100 rounded-full h-2">
                            <div class="bg-blue-500 h-2 rounded-full transition-all duration-1000" style="width: 45%">
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Memory</span>
                            <span class="text-sm font-medium text-gray-900">72%</span>
                        </div>
                        <div class="bg-gray-100 rounded-full h-2">
                            <div class="bg-yellow-500 h-2 rounded-full transition-all duration-1000"
                                style="width: 72%">
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-600">Storage</span>
                            <span class="text-sm font-medium text-gray-900">58%</span>
                        </div>
                        <div class="bg-gray-100 rounded-full h-2">
                            <div class="bg-green-500 h-2 rounded-full transition-all duration-1000"
                                style="width: 58%">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-hover-shadow bg-white rounded-xl p-6 card-shadow animate-slide-up">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Recent Activity</h3>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">New user registered</p>
                                <p class="text-xs text-gray-500">2 minutes ago</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Order #1234 completed</p>
                                <p class="text-xs text-gray-500">5 minutes ago</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3">
                            <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                                <svg class="w-4 h-4 text-yellow-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.732-.833-2.5 0L4.268 18.5c-.77.833.192 2.5 1.732 2.5z">
                                    </path>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Server maintenance</p>
                                <p class="text-xs text-gray-500">1 hour ago</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-hover-shadow bg-white rounded-xl p-6 card-shadow animate-slide-up">
                    <h3 class="text-lg font-semibold text-gray-900 mb-6">Quick Actions</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <button
                            class="bg-blue-50 hover:bg-blue-100 text-blue-600 p-4 rounded-lg transition-colors duration-200 flex flex-col items-center space-y-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                            </svg>
                            <span class="text-sm font-medium">Add User</span>
                        </button>
                        <button
                            class="bg-green-50 hover:bg-green-100 text-green-600 p-4 rounded-lg transition-colors duration-200 flex flex-col items-center space-y-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <span class="text-sm font-medium">New Report</span>
                        </button>
                        <button
                            class="bg-purple-50 hover:bg-purple-100 text-purple-600 p-4 rounded-lg transition-colors duration-200 flex flex-col items-center space-y-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z">
                                </path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                            </svg>
                            <span class="text-sm font-medium">Settings</span>
                        </button>
                        <button
                            class="bg-red-50 hover:bg-red-100 text-red-600 p-4 rounded-lg transition-colors duration-200 flex flex-col items-center space-y-2">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                </path>
                            </svg>
                            <span class="text-sm font-medium">Delete</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="max-w-7xl mx-auto mt-10">
            <div class="bg-white rounded-xl p-6 card-shadow">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">All Orders (Latest)</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead>
                            <tr class="text-left text-gray-600 border-b">
                                <th class="py-2 pr-4">#</th>
                                <th class="py-2 pr-4">Table</th>
                                <th class="py-2 pr-4">Type</th>
                                <th class="py-2 pr-4">Status</th>
                                <th class="py-2 pr-4">Amount</th>
                                <th class="py-2 pr-4">Created</th>
                                <th class="py-2 pr-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $o)
                                <tr class="border-b last:border-0">
                                    <td class="py-2 pr-4 font-medium">#{{ $o->order_number ?? $o->id }}</td>
                                    <td class="py-2 pr-4">{{ $o->table->name ?? '—' }}</td>
                                    <td class="py-2 pr-4 capitalize">{{ $o->order_type }}</td>
                                    <td class="py-2 pr-4 capitalize">
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs
                    @class([
                        'bg-yellow-100 text-yellow-700' => $o->status === 'pending',
                        'bg-green-100 text-green-700' => $o->status === 'served',
                        'bg-gray-100 text-gray-700' => !in_array($o->status, ['pending', 'served']),
                    ])
                  ">{{ $o->status }}</span>
                                    </td>
                                    <td class="py-2 pr-4">₹{{ number_format($o->total_amount, 2) }}</td>
                                    <td class="py-2 pr-4">{{ $o->created_at->format('d-m, h:i A') }}</td>
                                    <td class="py-2 pr-0 text-right">
                                        <a href="{{ route('restaurant.bill.print', $o->id) }}" target="_blank"
                                            rel="noopener"
                                            class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-indigo-600 text-white hover:bg-indigo-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M6 9V2h12v7M6 18h12v4H6v-4zM6 14H4a2 2 0 01-2-2V9a2 2 0 012-2h16a2 2 0 012 2v3a2 2 0 01-2 2h-2" />
                                            </svg>
                                            Print
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="py-6 text-center text-gray-500">No orders yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</div>
