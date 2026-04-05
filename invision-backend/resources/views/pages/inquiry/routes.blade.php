<x-layouts.app title="Route Inquiry">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Route Inquiry</h1>
        <p class="mt-1 text-sm text-gray-600">Browse route instances with completion metrics.</p>
    </div>

    {{-- Filters --}}
    <div class="bg-white shadow rounded-lg p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-5">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Statuses</option>
                    @foreach (\App\Enums\RouteStatus::cases() as $s)
                        <option value="{{ $s->value }}" @selected(($filters['status'] ?? '') === $s->value)>{{ $s->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">User</label>
                <select name="user_id" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Users</option>
                    @foreach (\App\Models\User::where('is_active', true)->orderBy('first_name')->get() as $u)
                        <option value="{{ $u->id }}" @selected(($filters['user_id'] ?? '') == $u->id)>{{ $u->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">From</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">To</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">Filter</button>
                <a href="{{ route('inquiry.routes') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300">Reset</a>
            </div>
        </form>
    </div>

    {{-- Results Table --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200">
            <span class="text-sm text-gray-600">{{ $routes->count() }} route instances found</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Route</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Visits</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Completion</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Started</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Completed</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Distance</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($routes as $route)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $route['route_name'] ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $route['user'] ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $route['route_date'] }}</td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusColors = ['pending' => 'gray', 'in_progress' => 'blue', 'completed' => 'green', 'cancelled' => 'red'];
                                $color = $statusColors[$route['status']] ?? 'gray';
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">{{ str_replace('_', ' ', ucfirst($route['status'])) }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-center text-gray-600">{{ $route['completed_visits'] }} / {{ $route['total_visits'] }}</td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center">
                                <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ min($route['completion_pct'], 100) }}%"></div>
                                </div>
                                <span class="text-xs text-gray-600">{{ $route['completion_pct'] }}%</span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $route['started_at'] ? \Carbon\Carbon::parse($route['started_at'])->format('H:i') : '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $route['completed_at'] ? \Carbon\Carbon::parse($route['completed_at'])->format('H:i') : '-' }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600">{{ $route['distance_km'] ? number_format($route['distance_km'], 1).' km' : '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-400">No route instances found matching your criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
