<x-layouts.app title="Route Instances">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Route Instances</h1>
            <p class="mt-1 text-sm text-gray-600">Daily route execution history and progress tracking.</p>
        </div>
        <a href="{{ route('routes.index') }}" class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
            Back to Plans
        </a>
    </div>

    <div class="bg-white shadow rounded-lg mb-6 p-4">
        <form method="GET" action="{{ route('route-instances.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="min-w-[160px]">
                <label class="block text-sm font-medium text-gray-700">Date From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div class="min-w-[160px]">
                <label class="block text-sm font-medium text-gray-700">Date To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div class="min-w-[140px]">
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">All</option>
                    @foreach(App\Enums\RouteStatus::cases() as $status)
                        <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm hover:bg-gray-700">Filter</button>
            <a href="{{ route('route-instances.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-50">Reset</a>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Route Plan</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Duration</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($instances as $instance)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $instance->route_date->format('M d, Y') }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <a href="{{ route('routes.show', $instance->route_plan_id) }}" class="text-indigo-600 hover:text-indigo-900">{{ $instance->routePlan->name ?? '—' }}</a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $instance->user->full_name ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php $color = $instance->status->color(); @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                            {{ $instance->status->label() }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div class="flex items-center gap-2">
                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $instance->completionPercentage() }}%"></div>
                            </div>
                            <span>{{ $instance->completed_visits }}/{{ $instance->total_visits }}</span>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($instance->started_at && $instance->completed_at)
                            {{ $instance->started_at->diffForHumans($instance->completed_at, true) }}
                        @elseif($instance->started_at)
                            Started {{ $instance->started_at->diffForHumans() }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                        <a href="{{ route('route-instances.show', $instance) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">No route instances found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($instances->hasPages())
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $instances->withQueryString()->links() }}
        </div>
        @endif
    </div>
</x-layouts.app>
