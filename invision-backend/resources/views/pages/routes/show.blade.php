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
                    <span class="text-xs px-2 py-0.5 rounded {{ $rs->week_start ? 'bg-amber-100 text-amber-800' : 'bg-gray-200 text-gray-700' }}">
                        {{ $rs->week_start ? 'Week of '.$rs->week_start->format('M d').' · ' : ($rs->week_number && $route->frequency->cycleWeeks() > 1 ? "Week {$rs->week_number} · " : '') }}
                        {{ $rs->day_of_week ? now()->startOfWeek()->addDays($rs->day_of_week - 1)->format('l') : 'Every work day' }}
                    </span>
                    @if($rs->expected_duration_minutes)
                    <span class="text-xs text-gray-400">~{{ $rs->expected_duration_minutes }} min</span>
                    @endif
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Upcoming schedule preview (resolves recurrence + specific-week overrides) --}}
    @php
        $preview = collect(range(0, 13))->map(function ($offset) use ($route) {
            $day = today()->addDays($offset);
            $dayStores = $route->storesForDate($day);

            return [
                'label' => $day->format('D, M d'),
                'km' => App\Models\RoutePlan::pathKm($dayStores),
                'stores' => $dayStores->map(fn ($rs) => [
                    'name' => $rs->store->name,
                    'lat' => $rs->store->gps_latitude !== null ? (float) $rs->store->gps_latitude : null,
                    'lng' => $rs->store->gps_longitude !== null ? (float) $rs->store->gps_longitude : null,
                ])->values(),
            ];
        });
    @endphp
    <div class="bg-white shadow rounded-lg p-6 mb-6" x-data="routePreview(@js($preview))" x-init="init()">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-medium text-gray-900">Next 14 Days</h2>
            <span class="text-xs px-2 py-0.5 rounded {{ $route->optimize_order ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600' }}">
                {{ $route->optimize_order ? 'Order optimized by location' : 'Manual visit order' }}
            </span>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-7 gap-2">
            <template x-for="(day, i) in days" :key="i">
                <button type="button" @click="select(i)" class="text-left p-2 rounded border"
                        :class="i === selected ? 'border-indigo-500 ring-2 ring-indigo-200 bg-indigo-50' : (day.stores.length ? 'border-indigo-100 bg-indigo-50' : 'border-gray-100 bg-gray-50')">
                    <p class="text-xs font-semibold text-gray-700" x-text="day.label"></p>
                    <template x-for="(s, n) in day.stores" :key="n">
                        <p class="text-xs text-gray-600 truncate" x-text="`${n + 1}. ${s.name}`"></p>
                    </template>
                    <p class="text-xs text-gray-400" x-show="!day.stores.length">No visits</p>
                    <p class="text-xs text-indigo-600 mt-1" x-show="day.km > 0" x-text="`≈ ${day.km} km`"></p>
                </button>
            </template>
        </div>
        <div class="mt-4">
            <p class="text-sm text-gray-600 mb-2" x-text="selected === null ? '' : `${days[selected].label}: ${days[selected].stores.length} visits, ≈ ${days[selected].km} km straight-line`"></p>
            <div x-ref="map" class="h-96 rounded-lg border border-gray-200 z-0"></div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    @endpush
    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
    function routePreview(days) {
        return {
            days, selected: null, map: null, layer: null,
            init() {
                this.map = L.map(this.$refs.map).setView([24.7136, 46.6753], 10);
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors', maxZoom: 19 }).addTo(this.map);
                this.layer = L.layerGroup().addTo(this.map);
                const first = this.days.findIndex(d => d.stores.length);
                if (first >= 0) this.select(first);
            },
            select(i) {
                this.selected = i;
                this.layer.clearLayers();
                const pts = [];
                this.days[i].stores.forEach((s, n) => {
                    if (s.lat === null) return;
                    pts.push([s.lat, s.lng]);
                    L.marker([s.lat, s.lng], { icon: L.divIcon({ className: '', iconSize: [26, 26], iconAnchor: [13, 13],
                        html: `<div style="width:26px;height:26px;border-radius:50%;background:${n === 0 ? '#059669' : '#4f46e5'};color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.4)">${n + 1}</div>` }) })
                        .bindTooltip(Object.assign(document.createElement("span"), { textContent: s.name })).addTo(this.layer);
                });
                if (pts.length > 1) L.polyline(pts, { color: '#4f46e5', weight: 3, opacity: .7 }).addTo(this.layer);
                if (pts.length) this.map.fitBounds(L.latLngBounds(pts), { padding: [30, 30], maxZoom: 15 });
            },
        };
    }
    </script>
    @endpush

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
