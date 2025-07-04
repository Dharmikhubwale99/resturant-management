<div>
    <!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restaurant Table Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .table-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .table-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Legend -->
        <div class="flex justify-center gap-6 mb-8">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-green-300 rounded"></div>
                <span class="text-sm font-medium text-gray-700">Available</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-red-300 rounded"></div>
                <span class="text-sm font-medium text-gray-700">Occupied</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 bg-yellow-300 rounded"></div>
                <span class="text-sm font-medium text-gray-700">Reserved</span>
            </div>
        </div>

        <!-- Ground Floor Section -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 pb-2 border-b-2 border-blue-500 inline-block">Ground Floor</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
                <!-- Table 1 - Occupied -->
                <div class="table-card bg-red-300 rounded-2xl p-6 cursor-pointer relative" onclick="openModal('table1')">
                    <div class="absolute top-4 right-4 bg-white px-3 py-1 rounded-full text-xs font-medium text-gray-700">
                        10 Seats
                    </div>
                    
                    <div class="text-4xl font-bold text-gray-800 mb-2">1</div>
                    <div class="text-sm text-gray-600 mb-1">Ground Floor</div>
                    
                    <div class="absolute bottom-4 left-4 flex items-center gap-2">
                        <button class="bg-white p-2 rounded-full hover:bg-gray-100 transition-colors" onclick="event.stopPropagation(); editTable(1)">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                        <div class="bg-white px-2 py-1 rounded-full flex items-center gap-1">
                            <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12,6 12,12 16,14"></polyline>
                            </svg>
                            <span class="text-xs font-medium text-gray-700">1 day</span>
                        </div>
                    </div>
                </div>

                <!-- Table 2 - Occupied -->
                <div class="table-card bg-red-300 rounded-2xl p-6 cursor-pointer relative" onclick="openModal('table2')">
                    <div class="absolute top-4 right-4 bg-white px-3 py-1 rounded-full text-xs font-medium text-gray-700">
                        10 Seats
                    </div>
                    
                    <div class="text-4xl font-bold text-gray-800 mb-2">2</div>
                    <div class="text-sm text-gray-600 mb-1">Ground Floor</div>
                    
                    <div class="absolute bottom-4 left-4 flex items-center gap-2">
                        <button class="bg-white p-2 rounded-full hover:bg-gray-100 transition-colors" onclick="event.stopPropagation(); editTable(2)">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                        <div class="bg-white px-2 py-1 rounded-full flex items-center gap-1">
                            <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12,6 12,12 16,14"></polyline>
                            </svg>
                            <span class="text-xs font-medium text-gray-700">1 day</span>
                        </div>
                    </div>
                </div>

                <!-- Table 3 - Occupied -->
                <div class="table-card bg-red-300 rounded-2xl p-6 cursor-pointer relative" onclick="openModal('table3')">
                    <div class="absolute top-4 right-4 bg-white px-3 py-1 rounded-full text-xs font-medium text-gray-700">
                        10 Seats
                    </div>
                    
                    <div class="text-4xl font-bold text-gray-800 mb-2">3</div>
                    <div class="text-sm text-gray-600 mb-1">Ground Floor</div>
                    
                    <div class="absolute bottom-4 left-4 flex items-center gap-2">
                        <button class="bg-white p-2 rounded-full hover:bg-gray-100 transition-colors" onclick="event.stopPropagation(); editTable(3)">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                        <div class="bg-white px-2 py-1 rounded-full flex items-center gap-1">
                            <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12,6 12,12 16,14"></polyline>
                            </svg>
                            <span class="text-xs font-medium text-gray-700">1 day</span>
                        </div>
                    </div>
                </div>

                <!-- Table 4 - Available -->
                <div class="table-card bg-green-300 rounded-2xl p-6 cursor-pointer relative" onclick="openModal('table4')">
                    <div class="absolute top-4 right-4 bg-white px-3 py-1 rounded-full text-xs font-medium text-gray-700">
                        12 Seats
                    </div>
                    
                    <div class="text-4xl font-bold text-gray-800 mb-2">4</div>
                    <div class="text-sm text-gray-600 mb-1">Ground Floor</div>
                    <div class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Available</div>
                </div>

                <!-- Table 5 - Occupied -->
                <div class="table-card bg-red-300 rounded-2xl p-6 cursor-pointer relative" onclick="openModal('table5')">
                    <div class="absolute top-4 right-4 bg-white px-3 py-1 rounded-full text-xs font-medium text-gray-700">
                        5 Seats
                    </div>
                    
                    <div class="text-4xl font-bold text-gray-800 mb-2">5</div>
                    <div class="text-sm text-gray-600 mb-1">Ground Floor</div>
                    
                    <div class="absolute bottom-4 left-4 flex items-center gap-2">
                        <button class="bg-white p-2 rounded-full hover:bg-gray-100 transition-colors" onclick="event.stopPropagation(); editTable(5)">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                        <div class="bg-white px-2 py-1 rounded-full flex items-center gap-1">
                            <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12,6 12,12 16,14"></polyline>
                            </svg>
                            <span class="text-xs font-medium text-gray-700">19 hours</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Garden Section -->
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 pb-2 border-b-2 border-blue-500 inline-block">Garden</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-6">
                <!-- Garden Table 1 - Available -->
                <div class="table-card bg-green-300 rounded-2xl p-6 cursor-pointer relative" onclick="openModal('garden1')">
                    <div class="absolute top-4 right-4 bg-white px-3 py-1 rounded-full text-xs font-medium text-gray-700">
                        4 Seats
                    </div>
                    
                    <div class="text-4xl font-bold text-gray-800 mb-2">1</div>
                    <div class="text-sm text-gray-600 mb-1">Garden</div>
                    <div class="text-sm font-semibold text-gray-800 uppercase tracking-wide">Available</div>
                </div>

                <!-- Garden Table 2 - Occupied -->
                <div class="table-card bg-red-300 rounded-2xl p-6 cursor-pointer relative" onclick="openModal('garden2')">
                    <div class="absolute top-4 right-4 bg-white px-3 py-1 rounded-full text-xs font-medium text-gray-700">
                        6 Seats
                    </div>
                    
                    <div class="text-4xl font-bold text-gray-800 mb-2">2</div>
                    <div class="text-sm text-gray-600 mb-1">Garden</div>
                    
                    <div class="absolute bottom-4 left-4 flex items-center gap-2">
                        <button class="bg-white p-2 rounded-full hover:bg-gray-100 transition-colors" onclick="event.stopPropagation(); editTable('garden2')">
                            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </button>
                        <div class="bg-white px-2 py-1 rounded-full flex items-center gap-1">
                            <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polyline points="12,6 12,12 16,14"></polyline>
                            </svg>
                            <span class="text-xs font-medium text-gray-700">1 day</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="tableModal" class="fixed inset-0 bg-black bg-opacity-50 backdrop-blur-sm hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-8 max-w-md w-full mx-4 shadow-2xl">
            <h2 id="modalTitle" class="text-2xl font-bold text-gray-800 mb-6 text-center">Table Information</h2>
            <div id="modalBody" class="text-center"></div>
            <button onclick="closeModal()" class="w-full mt-6 bg-gray-500 hover:bg-gray-600 text-white font-semibold py-3 px-6 rounded-xl transition-colors">
                Close
            </button>
        </div>
    </div>

    <script>
        let selectedTable = null;
        let tableData = {
            table1: { status: 'occupied', capacity: '10 Seats', area: 'Ground Floor', id: 1 },
            table2: { status: 'occupied', capacity: '10 Seats', area: 'Ground Floor', id: 2 },
            table3: { status: 'occupied', capacity: '10 Seats', area: 'Ground Floor', id: 3 },
            table4: { status: 'available', capacity: '12 Seats', area: 'Ground Floor', id: 4 },
            table5: { status: 'occupied', capacity: '5 Seats', area: 'Ground Floor', id: 5 },
            garden1: { status: 'available', capacity: '4 Seats', area: 'Garden', id: 'garden1' },
            garden2: { status: 'occupied', capacity: '6 Seats', area: 'Garden', id: 'garden2' }
        };

        function openModal(tableId) {
            selectedTable = tableData[tableId];
            const modal = document.getElementById('tableModal');
            const modalTitle = document.getElementById('modalTitle');
            const modalBody = document.getElementById('modalBody');

            modalTitle.textContent = `Table ${tableId.replace('table', '').replace('garden', 'Garden ')} - ${selectedTable.area}`;

            if (selectedTable.status === 'available') {
                modalBody.innerHTML = `
                    <p class="text-green-600 font-semibold mb-6">This table is available for booking.</p>
                    <button onclick="bookTable('${tableId}')" class="w-full bg-blue-500 hover:bg-blue-600 text-white font-semibold py-3 px-6 rounded-xl transition-colors mb-4">
                        Book Now
                    </button>
                `;
            } else {
                modalBody.innerHTML = `
                    <p class="text-red-600 font-semibold mb-6">Sorry, this table is currently ${selectedTable.status}.</p>
                `;
            }

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        function closeModal() {
            const modal = document.getElementById('tableModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            selectedTable = null;
        }

        function bookTable(tableId) {
            console.log(`Booking table ${tableId}`);
            alert(`Booking table ${tableId}...`);
            closeModal();
        }

        function editTable(tableId) {
            console.log(`Editing table ${tableId}`);
            alert(`Opening edit view for table ${tableId}...`);
        }

        // Close modal when clicking outside
        document.getElementById('tableModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>
</div>