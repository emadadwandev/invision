<x-layouts.app title="GPS Tracking">
    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <style>
        #tracking-map { height: 100%; min-height: 500px; }
        .pulse-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }
        .pulse-dot.online  { background: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.3); animation: pulse 2s infinite; }
        .pulse-dot.offline { background: #9ca3af; }
        @keyframes pulse { 0%,100%{box-shadow:0 0 0 3px rgba(16,185,129,.3)} 50%{box-shadow:0 0 0 8px rgba(16,185,129,.1)} }
    </style>
    @endpush

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">GPS Live Tracking</h1>
        <p class="mt-1 text-sm text-gray-600">Monitor field force locations in real time.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        {{-- User list sidebar --}}
        <div class="bg-white shadow rounded-lg p-4">
            <h2 class="text-sm font-medium text-gray-700 mb-3">Field Force</h2>
            <div class="space-y-2">
                @forelse($users as $user)
                <div class="flex items-center gap-3 p-2 rounded-md hover:bg-gray-50 cursor-pointer tracking-user-item"
                     data-user-id="{{ $user->id }}"
                     data-lat="{{ $user->latest_gps?->latitude }}"
                     data-lng="{{ $user->latest_gps?->longitude }}"
                     data-online="{{ $user->latest_gps && $user->latest_gps->recorded_at && $user->latest_gps->recorded_at->diffInMinutes(now()) < 30 ? '1' : '0' }}">
                    <span class="flex-shrink-0 inline-flex items-center justify-center h-8 w-8 rounded-full bg-indigo-100">
                        <span class="text-xs font-medium text-indigo-800">{{ substr($user->first_name, 0, 1) }}{{ substr($user->last_name, 0, 1) }}</span>
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $user->full_name }}</p>
                        <p class="text-xs text-gray-500">{{ $user->role->label() }}</p>
                    </div>
                    <span class="ml-auto flex-shrink-0 pulse-dot {{ $user->latest_gps && $user->latest_gps->recorded_at && $user->latest_gps->recorded_at->diffInMinutes(now()) < 30 ? 'online' : 'offline' }}"></span>
                </div>
                @empty
                <p class="text-sm text-gray-500">No field force users found.</p>
                @endforelse
            </div>
        </div>

        {{-- Leaflet Map --}}
        <div class="lg:col-span-3 bg-white shadow rounded-lg overflow-hidden" style="min-height: 500px;">
            <div id="tracking-map"></div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const map = L.map('tracking-map').setView([33.89, 35.50], 12);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(map);

            const markers = {};

            // Place a marker for each user that has GPS coords
            document.querySelectorAll('.tracking-user-item').forEach(item => {
                const userId = item.dataset.userId;
                const lat    = parseFloat(item.dataset.lat);
                const lng    = parseFloat(item.dataset.lng);
                const online = item.dataset.online === '1';
                const name   = item.querySelector('.text-sm.font-medium')?.textContent.trim() ?? '';
                const role   = item.querySelector('.text-xs.text-gray-500')?.textContent.trim() ?? '';

                if (!isNaN(lat) && !isNaN(lng)) {
                    const color = online ? '#10b981' : '#9ca3af';
                    const pulse = online ? 'box-shadow:0 0 0 6px rgba(16,185,129,.25);animation:pulse 2s infinite;' : '';
                    const icon = L.divIcon({
                        html: `<div style="width:32px;height:32px;background:${color};border:3px solid white;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,.4);${pulse}"><svg width="15" height="15" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg></div>`,
                        className: '',
                        iconSize: [32, 32],
                        iconAnchor: [16, 16],
                        popupAnchor: [0, -20],
                    });

                    const marker = L.marker([lat, lng], { icon })
                        .bindPopup(`<div style="min-width:140px;"><strong>${name}</strong><br><span style="font-size:12px;color:#6b7280;">${role}</span><br><span style="font-size:12px;color:${color};">● ${online ? 'Online' : 'Offline'}</span></div>`)
                        .addTo(map);

                    markers[userId] = marker;
                }

                // Click sidebar item → fly to user
                item.addEventListener('click', () => {
                    const lat = parseFloat(item.dataset.lat);
                    const lng = parseFloat(item.dataset.lng);
                    if (!isNaN(lat) && !isNaN(lng)) {
                        map.setView([lat, lng], 16, { animate: true });
                        markers[item.dataset.userId]?.openPopup();
                    }
                });
            });

            // Fit map to all markers
            const allCoords = Object.values(markers).map(m => m.getLatLng());
            if (allCoords.length > 0) {
                map.fitBounds(L.latLngBounds(allCoords), { padding: [40, 40] });
            }

            // Auto-refresh every 30 seconds
            setInterval(async () => {
                try {
                    const res  = await fetch('/command-center/field-force-json');
                    const json = await res.json();
                    json.data.forEach(user => {
                        if (!user.latitude || !user.longitude) return;
                        const m = markers[user.id];
                        if (m) {
                            m.setLatLng([user.latitude, user.longitude]);
                        }
                        const dot = document.querySelector(`.tracking-user-item[data-user-id="${user.id}"] .pulse-dot`);
                        if (dot) {
                            dot.className = 'ml-auto flex-shrink-0 pulse-dot ' + (user.is_online ? 'online' : 'offline');
                        }
                    });
                } catch (e) { /* silent */ }
            }, 30000);
        });
    </script>
    @endpush
</x-layouts.app>
