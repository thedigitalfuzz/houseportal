<div class="mt-8">

    @if (session()->has('success'))
        <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
            {{ session('success') }}
        </div>
    @endif
        <div class="bg-[#0f172a] text-white p-6 rounded-xl mb-6 flex items-center gap-4">
            @php
                $photoPath = asset('/images/hslogo.png');

                if(!empty($existingPhoto) && file_exists(storage_path('app/public/' . $existingPhoto))) {
                    $photoPath = asset('storage/' . $existingPhoto);
                }
            @endphp

            @if ($photo)
                <img src="{{ $photo->temporaryUrl() }}" class="w-16 h-16 rounded-full object-cover">
            @else
                <img src="{{ $photoPath }}" class="w-16 h-16 rounded-full object-cover">
            @endif

            <div>
                <h1 class="text-2xl font-bold">{{ $name }}</h1>
                <p class="text-gray-400">Admin Profile</p>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <div class="space-y-3 p-4 bg-white rounded-xl">
                <h3 class="text-xl font-bold mb-2">Edit Profile</h3>
                <!-- Name -->
                <div>
                    <label class="block font-medium">Name</label>
                    <input type="text" wire:model="name" class="w-full border rounded p-2" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>



                <!-- Passwords -->
                <div>
                    <label class="block font-medium">Current Password</label>
                    <input type="password" wire:model="current_password" class="w-full border rounded p-2" />
                    <x-input-error :messages="$errors->get('current_password')" class="mt-1" />
                </div>

                <div>
                    <label class="block font-medium">New Password</label>
                    <input type="password" wire:model="new_password" class="w-full border rounded p-2" />
                    <x-input-error :messages="$errors->get('new_password')" class="mt-1" />
                </div>

                <!-- Photo -->
                <div>
                    <label class="block font-medium">Photo</label>
                    <input type="file" wire:model="photo" class="w-full border rounded p-2" />

                    <!-- Preview new upload -->
                    @if ($photo)
                        <img src="{{ $photo->temporaryUrl() }}" class="mt-2 w-20 h-20 object-cover rounded-full" alt="Preview">

                        <!-- Show existing photo -->
                    @elseif ($existingPhoto)
                        <img src="{{ asset('storage/' . $existingPhoto) }}" class="mt-2 w-20 h-20 object-cover rounded-full" alt="Current Photo">
                    @endif

                    <x-input-error :messages="$errors->get('photo')" class="mt-1" />
                </div>
                <div class="mt-4 flex justify-start gap-2">
                    <a href="{{ route('dashboard') }}" class="px-4 py-2 border rounded bg-gray-500 text-white">Cancel</a>
                    <button wire:click="saveProfile" class="px-4 py-2 bg-green-600 text-white rounded">Save</button>
                </div>

            </div>
            <div class="flex flex-col space-y-3 justify-between">
                <!-- TODAY SUMMARY -->
                <div class="bg-[#0f172a] text-white p-4 rounded-xl">
                    <h2 class="text-lg font-bold mb-3">Today's Summary</h2>

                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>Txn: {{ $todaySummary['transactions'] }}</div>
                        <div>Players: {{ $todaySummary['players'] }}</div>
                        <div>Cash In: {{ $todaySummary['cashin'] }}</div>
                        <div>Cash Out: {{ $todaySummary['cashout'] }}</div>
                        <div class="col-span-2 font-bold">
                            Net: {{ $todaySummary['net'] }}
                        </div>
                    </div>
                </div>
                <div class="bg-[#0f172a] text-white p-4 rounded-xl">
                    <h2 class="font-bold mb-2">Monthly</h2>

                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>Txn: {{ $monthlySummary['transactions'] }}</div>
                        <div>Players: {{ $monthlySummary['players'] }}</div>
                        <div>Cash In: {{ $monthlySummary['cashin'] }}</div>
                        <div>Cash Out: {{ $monthlySummary['cashout'] }}</div>
                        <div class="col-span-2 font-bold">
                            Net: {{ $monthlySummary['net'] }}
                        </div>
                    </div>
                </div>

                <div class="bg-[#0f172a] text-white p-4 rounded-xl">
                    <h2 class="font-bold mb-2">All Time</h2>

                    <div class="grid grid-cols-2 gap-2 text-sm">
                        <div>Txn: {{ $allTimeSummary['transactions'] }}</div>
                        <div>Players: {{ $allTimeSummary['players'] }}</div>
                        <div>Cash In: {{ $allTimeSummary['cashin'] }}</div>
                        <div>Cash Out: {{ $allTimeSummary['cashout'] }}</div>
                        <div class="col-span-2 font-bold">
                            Net: {{ $allTimeSummary['net'] }}
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <div class="grid md:grid-cols-2 gap-4 mb-6">



        </div>
<div class="grid grid-cols-1">

    <div class="bg-white p-4 rounded-xl mb-6 overflow-x-auto" wire:ignore>
        <h3 class="text-xl font-bold mb-2">All Time CashIn Bar- Latest at the beginning</h3>
        <div style="width: {{ count($last10DaysLabels) * 60 }}px;"> <!-- 60px per bar -->
            <canvas id="last10DaysChart"
                    data-labels='@json($last10DaysLabels)'
                    data-data='@json($last10DaysData)'
                    height="300"></canvas>
        </div>
    </div>
</div>

<div class="grid grid-cols-1">
    <div class="bg-white p-4 rounded-xl mb-6 overflow-x-auto" wire:ignore>
        <h3 class="text-xl font-bold mb-2">All Time CashIn vs All Time CashOut</h3>
        <div class="overflow-x-auto" id="allTimeWrapper">
            <canvas id="allTimeChart"
                    data-labels='@json($allTimeLabels)'
                    data-cashin='@json($allTimeCashin)'
                    data-cashout='@json($allTimeCashout)'
                    class="w-full h-72" height="300"></canvas>
        </div>

    </div>
</div>


        <!-- TABLE (SCROLLABLE AFTER 15 ROWS) -->
        <div class="grid grid-cols-1">

            <div class="bg-white shadow rounded p-4 max-w-full">
                <h3 class="text-xl font-bold mb-2">Full Reports Table</h3>
                <!-- TABS -->
                <div class="flex gap-2 mb-4 bg-gray-100 p-1 rounded-lg max-w-full overflow-x-auto no-scrollbar">

                    <button wire:click="setTableTab('daily')"
                            class="flex-shrink-0 px-4 py-1.5 rounded-md text-sm font-medium transition
                    {{ $activeTableTab === 'daily'
                        ? 'bg-blue-600 text-white shadow'
                        : 'text-gray-600 hover:bg-white hover:shadow-sm' }}">
                        Daily
                    </button>

                    <button wire:click="setTableTab('monthly')"
                            class="flex-shrink-0 px-4 py-1.5 rounded-md text-sm font-medium transition
                    {{ $activeTableTab === 'monthly'
                        ? 'bg-blue-600 text-white shadow'
                        : 'text-gray-600 hover:bg-white hover:shadow-sm' }}">
                        Monthly
                    </button>

                    <button wire:click="setTableTab('all')"
                            class="flex-shrink-0 px-4 py-1.5 rounded-md text-sm font-medium transition
                    {{ $activeTableTab === 'all'
                        ? 'bg-blue-600 text-white shadow'
                        : 'text-gray-600 hover:bg-white hover:shadow-sm' }}">
                        All Time
                    </button>

                </div>

                <!-- TABLE -->
                <div class="overflow-x-auto max-w-full">
                    <div class="max-h-[400px] overflow-y-auto">
                        <table class="min-w-max w-full table-auto">
                            <thead class="bg-gray-100 sticky top-0 z-10">
                            <tr>
                                <th class="p-2 text-left w-1/4">Label</th>
                                <th class="p-2 text-right w-1/4">Cashin</th>
                                <th class="p-2 text-right w-1/4">Cashout</th>
                                <th class="p-2 text-right w-1/4">Net</th>
                            </tr>
                            </thead>
                            <tbody>
                            @php
                                switch($activeTableTab) {
                                    case 'monthly':
                                        $data = $monthlyTable;
                                        break;
                                    case 'all':
                                        $data = $allTimeTable;
                                        break;
                                    default:
                                        $data = $dailyTable;
                                }
                            @endphp

                            @foreach($data as $row)
                                <tr class="border-t">
                                    <td class="p-2">{{ $row['label'] }}</td>
                                    <td class="p-2 text-right">${{ number_format($row['cashin'], 2) }}</td>
                                    <td class="p-2 text-right">${{ number_format($row['cashout'], 2) }}</td>
                                    @php $net = $row['cashin'] - $row['cashout']; @endphp
                                    <td class="p-2 text-right {{ $net < 0 ? 'text-red-600' : 'text-green-600' }}">
                                        {{ $net < 0 ? '-$'.number_format(abs($net),2) : '$'.number_format($net,2) }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>

        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Wait 50ms to ensure canvas has layout
                setTimeout(() => {

                    // LAST 10 DAYS
                    const lastCanvas = document.getElementById('last10DaysChart');
                    const scrollWrapper = document.getElementById('scroll-wrapper');
                    const lastLabels = JSON.parse(lastCanvas.dataset.labels);
                    const lastData = JSON.parse(lastCanvas.dataset.data);

                    new Chart(lastCanvas, {
                        type: 'bar',
                        data: {
                            labels: lastLabels,
                            datasets: [{
                                label: 'Cash In',
                                data: lastData,
                                backgroundColor: '#3b82f6'
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '$' + value; // append $ to y-axis
                                        }
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return '$' + context.raw; // append $ in tooltip
                                        }
                                    }
                                }
                            }
                        }
                    });

                    // ALL TIME
                    const allCanvas = document.getElementById('allTimeChart');
                    const allLabels = JSON.parse(allCanvas.dataset.labels);
                    const allCashin = JSON.parse(allCanvas.dataset.cashin);
                    const allCashout = JSON.parse(allCanvas.dataset.cashout);

                    new Chart(allCanvas, {
                        type: 'line',
                        data: {
                            labels: allLabels,
                            datasets: [
                                { label: 'Cash In', data: allCashin, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.2)', tension: 0.3 },
                                { label: 'Cash Out', data: allCashout, borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.2)', tension: 0.3 }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(value) {
                                            return '$' + value; // y-axis $ sign
                                        }
                                    }
                                }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(context) {
                                            return '$' + context.raw; // tooltip $ sign
                                        }
                                    }
                                }
                            }
                        }
                    });

                    // Scroll to show latest 10 bars by default
                    const barWidth = 60; // must match the div width calculation
                    const visibleBars = 10;
                    const totalWidth = lastLabels.length * barWidth;
                    const scrollPos = totalWidth - (visibleBars * barWidth);
                    scrollWrapper.parentElement.scrollLeft = scrollPos;
                }, 50);
            });
        </script>
</div>
