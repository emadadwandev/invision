<x-layouts.app title="Route Instance - {{ $instance->route_date->format('M d, Y') }}">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('route-instances.index') }}" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">{{ $instance->routePlan->name ?? 'Route' }} — {{ $instance->route_date->format('M d, Y') }}</h1>
                @php $color = $instance->status->color(); @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                    {{ $instance->status->label() }}
                </span>
            </div>
            <p class="mt-1 text-sm text-gray-600">User: {{ $instance->user->full_name ?? '—' }}</p>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white shadow rounded-lg p-4">
            <p class="text-sm font-medium text-gray-500">Progress</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ $instance->completionPercentage() }}%</p>
            <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                <div class="bg-indigo-600 h-2 rounded-full" style="width: {{ $instance->completionPercentage() }}%"></div>
            </div>
        </div>
        <div class="bg-white shadow rounded-lg p-4">
            <p class="text-sm font-medium text-gray-500">Visits</p>
            <p class="mt-1 text-2xl font-bold text-gray-900">{{ $instance->completed_visits }} / {{ $instance->total_visits }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-4">
            <p class="text-sm font-medium text-gray-500">Started At</p>
            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $instance->started_at?->format('H:i') ?? '—' }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-4">
            <p class="text-sm font-medium text-gray-500">Distance</p>
            <p class="mt-1 text-lg font-semibold text-gray-900">{{ $instance->total_distance_km ? $instance->total_distance_km . ' km' : '—' }}</p>
        </div>
    </div>

    {{-- Visit List --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Store Visits</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Store</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check In</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Check Out</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Duration</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Distance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($instance->visits as $visit)
                <tr>
                    <td class="px-6 py-4 text-sm font-bold text-gray-400">{{ $visit->visit_order }}</td>
                    <td class="px-6 py-4">
                        <p class="text-sm font-medium text-gray-900">{{ $visit->store->name }}</p>
                        <p class="text-xs text-gray-500">{{ $visit->store->address ?? '' }}</p>
                    </td>
                    <td class="px-6 py-4">
                        @php $vc = $visit->status->color(); @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $vc }}-100 text-{{ $vc }}-800">
                            {{ $visit->status->label() }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $visit->checked_in_at?->format('H:i') ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $visit->checked_out_at?->format('H:i') ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $visit->duration_minutes ? $visit->duration_minutes . ' min' : '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                        @if($visit->checkin_distance_meters !== null)
                            {{ number_format($visit->checkin_distance_meters, 0) }}m
                            @if($visit->checkin_distance_meters <= 5)
                                <span class="text-green-500">✓</span>
                            @else
                                <span class="text-red-500">✗</span>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                </tr>
                @if($visit->notes)
                <tr>
                    <td></td>
                    <td colspan="6" class="px-6 pb-3 text-xs text-gray-400">Note: {{ $visit->notes }}</td>
                </tr>
                @endif
                @if($visit->skip_reason)
                <tr>
                    <td></td>
                    <td colspan="6" class="px-6 pb-3 text-xs text-red-400">Skipped: {{ $visit->skip_reason }}</td>
                </tr>
                @endif
                @endforeach
            </tbody>
        </table>
    </div>
</x-layouts.app>
