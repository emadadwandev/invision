<x-layouts.app title="{{ $route->name }}">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('routes.index') }}" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">{{ $route->name }}</h1>
                @php $color = $route->status->color(); @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                    {{ $route->status->label() }}
                </span>
            </div>
            <p class="mt-1 text-sm text-gray-600">{{ $route->description ?? 'No description' }}</p>
        </div>
        <div class="flex gap-2 mt-4 sm:mt-0">
            @can('update', $route)
            <a href="{{ route('routes.edit', $route) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Edit</a>
            @endcan
            @can('delete', $route)
            <form method="POST" action="{{ route('routes.destroy', $route) }}" onsubmit="return confirm('Delete this route plan?')">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50">Delete</button>
            </form>
            @endcan
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-md p-4">
        <p class="text-sm text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Route Info --}}
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Route Information</h2>
        <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">Assigned To</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $route->assignedUser->full_name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Frequency</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $route->frequency->label() }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Total Stores</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $route->total_stores }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $route->start_date->format('M d, Y') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">End Date</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $route->end_date?->format('M d, Y') ?? 'Ongoing' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Created</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $route->created_at->format('M d, Y H:i') }}</dd>
            </div>
        </dl>
    </div>

    {{-- Store Sequence --}}
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Store Visit Sequence</h2>
        @if($route->routeStores->isEmpty())
            <p class="text-sm text-gray-500">No stores assigned to this route.</p>
        @else
            <div class="space-y-2">
                @foreach($route->routeStores as $rs)
                <div class="flex items-center gap-4 p-3 bg-gray-50 rounded-lg">
                    <span class="flex items-center justify-center w-8 h-8 rounded-full bg-indigo-100 text-indigo-800 text-sm font-bold">{{ $rs->visit_order }}</span>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-900">{{ $rs->store->name }}</p>
                        <p class="text-xs text-gray-500">{{ $rs->store->code }} &middot; {{ $rs->store->address ?? '' }}</p>
                    </div>
                    @if($rs->expected_duration_minutes)
                    <span class="text-xs text-gray-400">~{{ $rs->expected_duration_minutes }} min</span>
                    @endif
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Generate Instance --}}
    @can('update', $route)
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Generate Route Instance</h2>
        <p class="text-sm text-gray-500 mb-3">Create a new daily route instance from this plan for the assigned user.</p>
        <form method="POST" action="{{ route('routes.create-instance', $route) }}" class="flex gap-3 items-end">
            @csrf
            <div>
                <label for="route_date" class="block text-sm font-medium text-gray-700">Date</label>
                <input type="date" name="route_date" id="route_date" value="{{ now()->toDateString() }}" required
                       class="mt-1 block rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700">Generate</button>
        </form>
    </div>
    @endcan

    {{-- Recent Instances --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Recent Instances</h2>
        @if($route->instances->isEmpty())
            <p class="text-sm text-gray-500">No instances generated yet.</p>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($route->instances as $instance)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $instance->route_date->format('M d, Y') }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $instance->user->full_name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @php $ic = $instance->status->color(); @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $ic }}-100 text-{{ $ic }}-800">
                                {{ $instance->status->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">
                            {{ $instance->completed_visits }}/{{ $instance->total_visits }}
                            ({{ $instance->completionPercentage() }}%)
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('route-instances.show', $instance) }}" class="text-sm text-indigo-600 hover:text-indigo-900">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layouts.app>
