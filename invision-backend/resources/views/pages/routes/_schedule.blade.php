{{-- Shared by create/edit. Expects: $stores, $frequency, $workDays, $optimizeOrder, $initialStores --}}
@php
    $days = [7 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
    $cycles = collect(App\Enums\VisitFrequency::cases())->mapWithKeys(fn ($f) => [$f->value => $f->cycleWeeks()]);
    $storeGeo = $stores->mapWithKeys(fn ($s) => [$s->id => [
        'name' => $s->name,
        'lat' => $s->gps_latitude !== null ? (float) $s->gps_latitude : null,
        'lng' => $s->gps_longitude !== null ? (float) $s->gps_longitude : null,
    ]]);
@endphp

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
@endpush

<div class="bg-white shadow rounded-lg p-6"
     x-data="routeSchedule(@js($initialStores), @js($frequency), @js($cycles), @js($storeGeo))" x-init="initMap()">
    <h2 class="text-lg font-medium text-gray-900 mb-4">Schedule</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label for="frequency" class="block text-sm font-medium text-gray-700">Frequency <span class="text-red-500">*</span></label>
            <select name="frequency" id="frequency" x-model="frequency" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @foreach(App\Enums\VisitFrequency::cases() as $freq)
                    <option value="{{ $freq->value }}">{{ $freq->label() }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500" x-show="frequency === 'daily'">Same stores every work day.</p>
            <p class="mt-1 text-xs text-gray-500" x-show="frequency === 'weekly'">Pick the visit days for each store, and the week repeats every week.</p>
            <p class="mt-1 text-xs text-gray-500" x-show="cycle > 1">Dynamic route: <span x-text="cycle"></span> different weeks rotate in a cycle. Set the week and days for each store.</p>
            @error('frequency') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <span class="block text-sm font-medium text-gray-700">Work Days</span>
            <div class="mt-2 flex flex-wrap gap-3">
                @foreach($days as $num => $label)
                    <label class="inline-flex items-center gap-1 text-sm text-gray-700">
                        <input type="checkbox" name="work_days[]" value="{{ $num }}" {{ in_array($num, $workDays) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            <p class="mt-1 text-xs text-gray-500">Stores with no visit days picked are visited on every work day.</p>
        </div>
        <div class="md:col-span-2">
            <input type="hidden" name="optimize_order" value="0">
            <label class="inline-flex items-start gap-2 text-sm text-gray-700">
                <input type="checkbox" name="optimize_order" value="1" {{ $optimizeOrder ? 'checked' : '' }}
                       class="mt-0.5 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <span>
                    <span class="font-medium">Optimize visit order by store location</span><br>
                    <span class="text-xs text-gray-500">Each day starts at the first store in the list, then follows the shortest path through the rest. Turn off to use the exact order below.</span>
                </span>
            </label>
        </div>
    </div>

    <h3 class="text-sm font-semibold text-gray-700 mb-1">Store Visits</h3>
    <p class="text-sm text-gray-500 mb-4">
        Pick one or more visit days per store. To visit a store again in a different week of the cycle, add it on another row.
        Use <strong>Specific week</strong> when one week is different: the stores you add for that week replace the regular schedule for that week only.
    </p>

    <template x-for="(item, index) in stores" :key="item._key">
        <div class="mb-3 p-3 rounded-lg" :class="item.week_start ? 'bg-amber-50' : 'bg-gray-50'">
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex flex-col">
                    <button type="button" @click="move(index, -1)" :disabled="index === 0" class="text-gray-400 hover:text-gray-700 disabled:opacity-30 leading-none" title="Move up">▲</button>
                    <button type="button" @click="move(index, 1)" :disabled="index === stores.length - 1" class="text-gray-400 hover:text-gray-700 disabled:opacity-30 leading-none" title="Move down">▼</button>
                </div>
                <span class="text-sm font-bold text-gray-400 w-6" x-text="index + 1"></span>
                <select :name="`stores[${index}][store_id]`" x-model="item.store_id" @change="drawMap()" required
                        class="flex-1 min-w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Select store...</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}">{{ $store->name }} ({{ $store->code }}){{ $store->gps_latitude === null ? ' · no GPS' : '' }}</option>
                    @endforeach
                </select>
                <select :name="`stores[${index}][week_number]`" x-model="item.week_number" x-show="cycle > 1 && !item.week_start"
                        class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Every week</option>
                    <template x-for="w in cycle" :key="w">
                        <option :value="w" x-text="`Week ${w}`" :selected="String(item.week_number) === String(w)"></option>
                    </template>
                </select>
                <label class="flex items-center gap-1 text-xs text-gray-500">
                    Specific week
                    <input type="date" :name="`stores[${index}][week_start]`" x-model="item.week_start" title="Any date in the week to override"
                           class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </label>
                <input type="hidden" :name="`stores[${index}][visit_order]`" :value="index + 1">
                <input type="number" :name="`stores[${index}][expected_duration_minutes]`" x-model="item.duration"
                       placeholder="Min" min="1" max="480"
                       class="w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <button type="button" @click="stores.splice(index, 1); drawMap()" class="text-red-500 hover:text-red-700" title="Remove">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
            <div class="mt-2 ml-14 flex flex-wrap gap-1" x-show="frequency !== 'daily'">
                @foreach($days as $num => $label)
                    <label class="cursor-pointer">
                        <input type="checkbox" class="sr-only peer" :name="`stores[${index}][days][]`" value="{{ $num }}"
                               :checked="item.days.includes({{ $num }})" @change="toggleDay(item, {{ $num }})">
                        <span class="inline-block px-2 py-0.5 rounded border text-xs border-gray-300 text-gray-600 peer-checked:bg-indigo-600 peer-checked:border-indigo-600 peer-checked:text-white">{{ $label }}</span>
                    </label>
                @endforeach
                <span class="text-xs text-gray-400 self-center ml-1" x-show="item.days.length === 0">Every work day</span>
                <span class="text-xs text-indigo-600 self-center ml-1" x-show="item.days.length > 1" x-text="`${item.days.length} visits / week`"></span>
            </div>
        </div>
    </template>

    <button type="button" @click="stores.push(blank()); drawMap()"
            class="mt-2 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
        + Add Store to Route
    </button>

    {{-- Map of the planned stores --}}
    <div class="mt-6">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-sm font-semibold text-gray-700">Store Locations</h3>
            <select x-model="mapDay" @change="drawMap()" class="rounded-md border-gray-300 text-sm">
                <option value="">All days</option>
                @foreach($days as $num => $label)
                    <option value="{{ $num }}">{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div x-ref="map" class="h-80 rounded-lg border border-gray-200 z-0"></div>
        <p class="mt-1 text-xs text-gray-500">Numbers follow the list order. The route page shows each day's final (optimized) order and distance.</p>
    </div>
</div>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
function routeSchedule(stores, frequency, cycles, geo) {
    let key = 0;
    return {
        // old() input uses the request field names; normalise so every row has all keys
        stores: stores.map(s => ({
            store_id: '', week_number: '', week_start: '', ...s,
            days: (s.days ?? (s.day_of_week ? [s.day_of_week] : [])).map(Number),
            duration: s.duration ?? s.expected_duration_minutes ?? '',
            _key: key++,
        })),
        frequency,
        mapDay: '',
        map: null,
        layer: null,
        get cycle() { return cycles[this.frequency] ?? 1; },
        blank() { return { store_id: '', days: [], week_number: '', week_start: '', duration: '', _key: key++ }; },
        toggleDay(item, d) {
            item.days = item.days.includes(d) ? item.days.filter(x => x !== d) : [...item.days, d];
            this.drawMap();
        },
        move(i, dir) {
            const [row] = this.stores.splice(i, 1);
            this.stores.splice(i + dir, 0, row);
            this.drawMap();
        },
        initMap() {
            this.map = L.map(this.$refs.map).setView([24.7136, 46.6753], 10);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap contributors', maxZoom: 19 }).addTo(this.map);
            this.layer = L.layerGroup().addTo(this.map);
            this.drawMap();
        },
        drawMap() {
            if (!this.layer) return;
            this.layer.clearLayers();
            const day = Number(this.mapDay);
            const pts = [];
            this.stores
                .filter(s => !day || s.days.length === 0 || s.days.includes(day))
                .forEach((s, i) => {
                    const g = geo[s.store_id];
                    if (!g || g.lat === null) return;
                    pts.push([g.lat, g.lng]);
                    L.marker([g.lat, g.lng], { icon: L.divIcon({ className: '', iconSize: [24, 24], iconAnchor: [12, 12],
                        html: `<div style="width:24px;height:24px;border-radius:50%;background:#4f46e5;color:#fff;font-size:11px;font-weight:700;display:flex;align-items:center;justify-content:center;border:2px solid #fff;box-shadow:0 1px 4px rgba(0,0,0,.4)">${i + 1}</div>` }) })
                        .bindTooltip(Object.assign(document.createElement("span"), { textContent: g.name })).addTo(this.layer);
                });
            if (day && pts.length > 1) L.polyline(pts, { color: '#4f46e5', weight: 3, opacity: .6, dashArray: '6 4' }).addTo(this.layer);
            if (pts.length) this.map.fitBounds(L.latLngBounds(pts), { padding: [30, 30], maxZoom: 15 });
        },
    };
}
</script>
@endpush
