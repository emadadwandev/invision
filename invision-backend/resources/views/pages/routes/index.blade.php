<x-layouts.app title="Route Plans">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Route Plans</h1>
            <p class="mt-1 text-sm text-gray-600">Manage route plans and store visit sequences.</p>
        </div>
        <div class="flex gap-2 mt-4 sm:mt-0">
            <a href="{{ route('route-instances.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                View Instances
            </a>
            @can('create', App\Models\RoutePlan::class)
            <a href="{{ route('routes.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                + New Route Plan
            </a>
            @endcan
        </div>
    </div>

    <div class="bg-white shadow rounded-lg mb-6 p-4">
        <form method="GET" action="{{ route('routes.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Route plan name..."
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
            <div class="min-w-[140px]">
                <label class="block text-sm font-medium text-gray-700">Frequency</label>
                <select name="frequency" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">All</option>
                    @foreach(App\Enums\VisitFrequency::cases() as $freq)
                        <option value="{{ $freq->value }}" {{ request('frequency') === $freq->value ? 'selected' : '' }}>{{ $freq->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm hover:bg-gray-700">Filter</button>
            <a href="{{ route('routes.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-50">Reset</a>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Frequency</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stores</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($plans as $plan)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('routes.show', $plan) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600">{{ $plan->name }}</a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $plan->assignedUser->full_name ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $plan->frequency->label() }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $plan->total_stores }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $plan->start_date->format('M d, Y') }}
                        @if($plan->end_date) — {{ $plan->end_date->format('M d, Y') }} @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @php $color = $plan->status->color(); @endphp
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                            {{ $plan->status->label() }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                        <a href="{{ route('routes.show', $plan) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                        @can('update', $plan)
                        <a href="{{ route('routes.edit', $plan) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">No route plans found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($plans->hasPages())
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $plans->withQueryString()->links() }}
        </div>
        @endif
    </div>
</x-layouts.app>
