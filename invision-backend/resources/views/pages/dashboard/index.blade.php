<x-layouts.app title="Dashboard">
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Dashboard</h1>
            <p class="mt-1 text-sm text-gray-600">Welcome back, {{ $user->first_name }}.</p>
        </div>
        <form method="GET" class="mt-3 sm:mt-0">
            <select name="period" onchange="this.form.submit()"
                    class="rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                @foreach (['week' => 'This Week', 'month' => 'This Month', 'quarter' => 'This Quarter', 'year' => 'This Year'] as $val => $label)
                    <option value="{{ $val }}" @selected($period === $val)>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </div>

    {{-- Overview KPI Cards --}}
    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
        @php $kpis = [
            ['label' => 'Total Users', 'value' => $overview['total_users'], 'color' => 'indigo', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z'],
            ['label' => 'Field Force', 'value' => $overview['field_force_count'], 'color' => 'blue', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
            ['label' => 'Online Now', 'value' => $overview['online_now'], 'color' => 'green', 'icon' => 'M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728m-9.9-2.829a5 5 0 010-7.07m7.072 0a5 5 0 010 7.07M13 12a1 1 0 11-2 0 1 1 0 012 0z'],
            ['label' => 'Active Stores', 'value' => $overview['total_stores'], 'color' => 'purple', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
            ['label' => 'Active Campaigns', 'value' => $overview['active_campaigns'], 'color' => 'amber', 'icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.957a1 1 0 00.95.69h4.162c.969 0 1.371 1.24.588 1.81l-3.37 2.448a1 1 0 00-.364 1.118l1.287 3.957c.3.921-.755 1.688-1.54 1.118l-3.37-2.448a1 1 0 00-1.176 0l-3.37 2.448c-.784.57-1.838-.197-1.539-1.118l1.287-3.957a1 1 0 00-.364-1.118L2.05 9.384c-.783-.57-.38-1.81.588-1.81h4.162a1 1 0 00.95-.69l1.3-3.957z'],
        ]; @endphp
        @foreach ($kpis as $kpi)
        <div class="bg-white shadow rounded-lg p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0 p-2 rounded-full bg-{{ $kpi['color'] }}-100">
                    <svg class="h-5 w-5 text-{{ $kpi['color'] }}-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $kpi['icon'] }}" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-xs font-medium text-gray-500">{{ $kpi['label'] }}</p>
                    <p class="text-lg font-bold text-gray-900">{{ number_format($kpi['value']) }}</p>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Today Stats --}}
    <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-4">
        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-lg p-4 text-white">
            <p class="text-xs font-medium opacity-80">Today Visits</p>
            <p class="text-2xl font-bold mt-1">{{ number_format($overview['today_visits']) }}</p>
        </div>
        <div class="bg-gradient-to-br from-blue-500 to-cyan-600 rounded-lg p-4 text-white">
            <p class="text-xs font-medium opacity-80">Today Orders</p>
            <p class="text-2xl font-bold mt-1">{{ number_format($overview['today_orders']) }}</p>
        </div>
        <div class="bg-gradient-to-br from-green-500 to-emerald-600 rounded-lg p-4 text-white">
            <p class="text-xs font-medium opacity-80">Today Sales</p>
            <p class="text-2xl font-bold mt-1">${{ number_format($overview['today_sales'], 2) }}</p>
        </div>
        <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-lg p-4 text-white">
            <p class="text-xs font-medium opacity-80">Today Collections</p>
            <p class="text-2xl font-bold mt-1">${{ number_format($overview['today_collections'], 2) }}</p>
        </div>
    </div>

    {{-- Sales & Routes Row --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Sales KPIs --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Sales Performance</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500">Total Revenue</p>
                    <p class="text-xl font-bold text-green-600">${{ number_format($sales['total_revenue'], 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Avg Order Value</p>
                    <p class="text-xl font-bold text-gray-900">${{ number_format($sales['avg_order_value'], 2) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Total Orders</p>
                    <p class="text-xl font-bold text-gray-900">{{ number_format($sales['total_orders']) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Delivered / Cancelled</p>
                    <p class="text-xl font-bold">
                        <span class="text-green-600">{{ $sales['delivered_count'] }}</span>
                        <span class="text-gray-400">/</span>
                        <span class="text-red-500">{{ $sales['cancelled_count'] }}</span>
                    </p>
                </div>
            </div>
            {{-- Daily Sales Chart --}}
            <div class="mt-4">
                <canvas id="salesTrendChart" height="150"></canvas>
            </div>
        </div>

        {{-- Route KPIs --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Route Performance</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <p class="text-xs text-gray-500">Route Completion</p>
                    <p class="text-xl font-bold text-indigo-600">{{ $routes['completion_rate'] }}%</p>
                    <p class="text-xs text-gray-400">{{ $routes['completed_instances'] }} / {{ $routes['total_route_instances'] }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Visit Completion</p>
                    <p class="text-xl font-bold text-blue-600">{{ $routes['visit_completion_rate'] }}%</p>
                    <p class="text-xs text-gray-400">{{ $routes['completed_visits'] }} / {{ $routes['total_visits'] }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Avg Visit Duration</p>
                    <p class="text-xl font-bold text-gray-900">{{ $routes['avg_visit_duration'] }} min</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Skipped Visits</p>
                    <p class="text-xl font-bold text-red-500">{{ $routes['skipped_visits'] }}</p>
                </div>
            </div>
            {{-- Top performers --}}
            @if(count($routes['top_performers']) > 0)
            <div class="mt-4">
                <h3 class="text-sm font-medium text-gray-700 mb-2">Top Performers</h3>
                <div class="space-y-2">
                    @foreach ($routes['top_performers']->take(5) as $perf)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-700">{{ $perf['name'] ?? 'N/A' }}</span>
                        <span class="font-medium text-indigo-600">{{ $perf['visit_count'] }} visits</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Campaign & POS / Credit Row --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Campaign KPIs --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Campaigns</h2>
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Budget Utilization</span>
                    <span class="font-semibold text-gray-900">{{ $campaigns['budget_utilization'] }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-amber-500 h-2 rounded-full" style="width: {{ min($campaigns['budget_utilization'], 100) }}%"></div>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Total Budget</span>
                    <span class="font-medium">${{ number_format($campaigns['total_budget'], 2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Spent</span>
                    <span class="font-medium">${{ number_format($campaigns['total_spent'], 2) }}</span>
                </div>
            </div>
            @if(count($campaigns['campaign_performance']) > 0)
            <div class="mt-4 border-t pt-3">
                <h3 class="text-sm font-medium text-gray-700 mb-2">Active Campaigns</h3>
                @foreach ($campaigns['campaign_performance']->take(3) as $cp)
                <div class="flex items-center justify-between text-sm py-1">
                    <span class="text-gray-700 truncate mr-2">{{ $cp['name'] }}</span>
                    <span class="font-medium text-amber-600">{{ $cp['task_completion'] }}%</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- POS KPIs --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">POS Activity</h2>
            <div class="space-y-3">
                <div>
                    <p class="text-xs text-gray-500">Total Transactions</p>
                    <p class="text-2xl font-bold text-gray-900">{{ number_format($pos['total_transactions']) }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Total Amount</p>
                    <p class="text-xl font-bold text-green-600">${{ number_format($pos['total_amount'], 2) }}</p>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div class="bg-blue-50 rounded p-2">
                        <p class="text-xs text-blue-600">Sell-Out</p>
                        <p class="text-sm font-bold text-blue-700">${{ number_format($pos['sell_out_amount'], 2) }}</p>
                    </div>
                    <div class="bg-purple-50 rounded p-2">
                        <p class="text-xs text-purple-600">Sell-Through</p>
                        <p class="text-sm font-bold text-purple-700">${{ number_format($pos['sell_through_amount'], 2) }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Credit KPIs --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Credit & Collections</h2>
            <div class="space-y-3">
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500">Credit Utilization</span>
                    <span class="font-semibold text-gray-900">{{ $credits['utilization_pct'] }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-red-500 h-2 rounded-full" style="width: {{ min($credits['utilization_pct'], 100) }}%"></div>
                </div>
                <div class="grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <p class="text-gray-500">Total Limit</p>
                        <p class="font-medium">${{ number_format($credits['total_credit_limit'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Outstanding</p>
                        <p class="font-medium text-red-600">${{ number_format($credits['total_balance'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Available</p>
                        <p class="font-medium text-green-600">${{ number_format($credits['total_available'], 2) }}</p>
                    </div>
                    <div>
                        <p class="text-gray-500">Accounts</p>
                        <p class="font-medium">{{ $credits['accounts_count'] }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Top Stores & Top Reps --}}
    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Top Stores --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Top Stores by Sales</h2>
            @if(count($sales['top_stores']) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 font-medium text-gray-500">#</th>
                            <th class="text-left py-2 font-medium text-gray-500">Store</th>
                            <th class="text-right py-2 font-medium text-gray-500">Orders</th>
                            <th class="text-right py-2 font-medium text-gray-500">Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sales['top_stores'] as $i => $store)
                        <tr class="border-b border-gray-100">
                            <td class="py-2 text-gray-400">{{ $i + 1 }}</td>
                            <td class="py-2 text-gray-700">{{ $store['store_name'] ?? 'N/A' }}</td>
                            <td class="py-2 text-right text-gray-600">{{ $store['order_count'] }}</td>
                            <td class="py-2 text-right font-medium text-green-600">${{ number_format($store['total_sales'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-sm text-gray-400">No sales data for this period.</p>
            @endif
        </div>

        {{-- Top Sales Reps --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Top Sales Representatives</h2>
            @if(count($sales['top_sales_reps']) > 0)
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            <th class="text-left py-2 font-medium text-gray-500">#</th>
                            <th class="text-left py-2 font-medium text-gray-500">Name</th>
                            <th class="text-right py-2 font-medium text-gray-500">Orders</th>
                            <th class="text-right py-2 font-medium text-gray-500">Sales</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($sales['top_sales_reps'] as $i => $rep)
                        <tr class="border-b border-gray-100">
                            <td class="py-2 text-gray-400">{{ $i + 1 }}</td>
                            <td class="py-2 text-gray-700">{{ $rep['name'] ?? 'N/A' }}</td>
                            <td class="py-2 text-right text-gray-600">{{ $rep['order_count'] }}</td>
                            <td class="py-2 text-right font-medium text-green-600">${{ number_format($rep['total_sales'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <p class="text-sm text-gray-400">No sales data for this period.</p>
            @endif
        </div>
    </div>

    {{-- Quick Navigation --}}
    <div class="mt-6 bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Inquiry Screens</h2>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <a href="{{ route('inquiry.stores') }}" class="flex items-center px-4 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <svg class="h-5 w-5 text-purple-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                Store Inquiry
            </a>
            <a href="{{ route('inquiry.sales') }}" class="flex items-center px-4 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <svg class="h-5 w-5 text-green-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" /></svg>
                Sales Inquiry
            </a>
            <a href="{{ route('inquiry.routes') }}" class="flex items-center px-4 py-3 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                <svg class="h-5 w-5 text-blue-500 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7" /></svg>
                Route Inquiry
            </a>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const trend = @json($sales['daily_trend']);
            if (trend.length > 0) {
                new Chart(document.getElementById('salesTrendChart'), {
                    type: 'line',
                    data: {
                        labels: trend.map(d => d.date),
                        datasets: [{
                            label: 'Daily Sales ($)',
                            data: trend.map(d => d.total),
                            borderColor: 'rgb(79, 70, 229)',
                            backgroundColor: 'rgba(79, 70, 229, 0.1)',
                            fill: true,
                            tension: 0.3,
                            pointRadius: 3,
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: { legend: { display: false } },
                        scales: {
                            x: { display: true, grid: { display: false } },
                            y: { display: true, beginAtZero: true, ticks: { callback: v => '$' + v.toLocaleString() } }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</x-layouts.app>
