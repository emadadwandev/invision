<x-layouts.app title="GPS Tracking">
    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
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
                <div class="flex items-center gap-3 p-2 rounded-md hover:bg-gray-50 cursor-pointer tracking-user-item" data-user-id="{{ $user['id'] }}">
                    <span class="flex-shrink-0 inline-flex items-center justify-center h-8 w-8 rounded-full bg-indigo-100">
                        <span class="text-xs font-medium text-indigo-800">{{ collect(explode(' ', $user['name']))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('') }}</span>
                    </span>
                    <div class="min-w-0">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ $user['name'] }}</p>
                        <p class="text-xs text-gray-500 last-seen">{{ $user['last_seen'] ? 'Last seen '.\Illuminate\Support\Carbon::parse($user['last_seen'])->diffForHumans() : 'No location yet' }}</p>
                    </div>
                    <span class="ml-auto flex-shrink-0 pulse-dot {{ $user['is_online'] ? 'online' : 'offline' }}"></span>
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
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const map = L.map('tracking-map').setView([24.7136, 46.6753], 11);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors',
                maxZoom: 19,
            }).addTo(map);

            const markers = {};
            let users = @json($users);

            const esc = (t) => { const d = document.createElement('div'); d.textContent = t ?? ''; return d.innerHTML; };

            function icon(online) {
                const color = online ? '#10b981' : '#9ca3af';
                const pulse = online ? 'box-shadow:0 0 0 6px rgba(16,185,129,.25);animation:pulse 2s infinite;' : '';
                return L.divIcon({
                    html: `<div style="width:32px;height:32px;background:${color};border:3px solid white;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,.4);${pulse}"><svg width="15" height="15" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg></div>`,
                    className: '', iconSize: [32, 32], iconAnchor: [16, 16], popupAnchor: [0, -20],
                });
            }

            function render() {
                users.forEach(user => {
                    const dot = document.querySelector(`.tracking-user-item[data-user-id="${user.id}"] .pulse-dot`);
                    if (dot) dot.className = 'ml-auto flex-shrink-0 pulse-dot ' + (user.is_online ? 'online' : 'offline');
                    if (!user.latitude || !user.longitude) return;

                    const popup = `<div style="min-width:140px;"><strong>${esc(user.name)}</strong><br><span style="font-size:12px;color:#6b7280;">${esc(user.role_label)}</span><br><span style="font-size:12px;">● ${user.is_online ? 'Online' : 'Offline'}</span>${user.last_seen ? `<br><span style="font-size:11px;color:#9ca3af;">${new Date(user.last_seen).toLocaleString()}</span>` : ''}</div>`;
                    if (markers[user.id]) {
                        markers[user.id].setLatLng([user.latitude, user.longitude]).setIcon(icon(user.is_online)).setPopupContent(popup);
                    } else {
                        markers[user.id] = L.marker([user.latitude, user.longitude], { icon: icon(user.is_online) }).bindPopup(popup).addTo(map);
                    }
                });
            }

            render();
            const coords = Object.values(markers).map(m => m.getLatLng());
            if (coords.length > 0) map.fitBounds(L.latLngBounds(coords), { padding: [40, 40], maxZoom: 16 });

            document.querySelectorAll('.tracking-user-item').forEach(item => {
                item.addEventListener('click', () => {
                    const m = markers[item.dataset.userId];
                    if (m) { map.setView(m.getLatLng(), 16, { animate: true }); m.openPopup(); }
                });
            });

            setInterval(async () => {
                try {
                    const res = await fetch('{{ route('command-center.field-force-json') }}', { headers: { Accept: 'application/json' } });
                    users = (await res.json()).data;
                    render();
                } catch (e) { /* silent */ }
            }, 30000);
        });
    </script>
    @endpush
</x-layouts.app>
