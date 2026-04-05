<x-layouts.app title="Command Center">
    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    <style>
        #command-map { height: calc(100vh - 280px); min-height: 500px; border-radius: 0.5rem; z-index: 0; }
        .store-popup { padding: 2px; min-width: 220px; }
        .store-popup h4 { margin: 0 0 8px; font-weight: 600; font-size: 14px; }
        .store-popup .stat-row { display: flex; justify-content: space-between; padding: 4px 0; font-size: 13px; border-bottom: 1px solid #f0f0f0; }
        .user-popup { padding: 2px; min-width: 200px; }
        .user-popup h4 { margin: 0 0 4px; font-weight: 600; font-size: 14px; }
        .pulse-dot { width: 12px; height: 12px; border-radius: 50%; display: inline-block; }
        .pulse-dot.online { background: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.3); animation: pulse 2s infinite; }
        .pulse-dot.offline { background: #9ca3af; }
        @keyframes pulse { 0%,100% { box-shadow: 0 0 0 3px rgba(16,185,129,.3); } 50% { box-shadow: 0 0 0 8px rgba(16,185,129,.1); } }
        .leaflet-popup-content-wrapper { border-radius: 8px; padding: 0; }
        .leaflet-popup-content { margin: 12px; }
    </style>
    @endpush

    <!-- Stats Bar -->
    <div class="mb-4 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-2xl font-bold text-indigo-600">{{ $stats['total_field_force'] }}</div>
            <div class="text-xs text-gray-500">Total Field Force</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-2xl font-bold text-green-600">{{ $stats['online_count'] }}</div>
            <div class="text-xs text-gray-500">Online Now</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-2xl font-bold text-blue-600">{{ $stats['active_routes'] }}</div>
            <div class="text-xs text-gray-500">Active Routes</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-2xl font-bold text-purple-600">{{ $stats['total_stores'] }}</div>
            <div class="text-xs text-gray-500">Total Stores</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-2xl font-bold text-orange-600">{{ $stats['today_orders'] }}</div>
            <div class="text-xs text-gray-500">Today Orders</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 text-center">
            <div class="text-2xl font-bold text-emerald-600">${{ number_format($stats['today_sales'], 2) }}</div>
            <div class="text-xs text-gray-500">Today Sales</div>
        </div>
    </div>

    <!-- Map + Sidebar -->
    <div class="flex gap-4" x-data="commandCenter()" x-init="init()">
        <!-- Sidebar: Field Force List -->
        <div class="w-80 flex-shrink-0 bg-white rounded-lg shadow overflow-hidden flex flex-col" style="height: calc(100vh - 280px); min-height: 500px;">
            <div class="p-3 border-b bg-gray-50">
                <h3 class="text-sm font-semibold text-gray-700">Field Force</h3>
                <input type="text" x-model="searchUser" placeholder="Search..."
                       class="mt-2 w-full text-sm border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="overflow-y-auto flex-1">
                <template x-for="user in filteredUsers" :key="user.id">
                    <div class="flex items-center gap-3 px-3 py-2 border-b border-gray-100 hover:bg-indigo-50 cursor-pointer transition"
                         @click="focusUser(user)">
                        <span class="pulse-dot" :class="user.is_online ? 'online' : 'offline'"></span>
                        <div class="min-w-0 flex-1">
                            <div class="text-sm font-medium text-gray-900 truncate" x-text="user.name"></div>
                            <div class="text-xs text-gray-500" x-text="user.role_label"></div>
                        </div>
                        <template x-if="user.speed_kmh">
                            <span class="text-xs text-gray-400" x-text="Number(user.speed_kmh).toFixed(1) + ' km/h'"></span>
                        </template>
                    </div>
                </template>
                <template x-if="filteredUsers.length === 0">
                    <div class="p-4 text-sm text-gray-400 text-center">No field force found.</div>
                </template>
            </div>
        </div>

        <!-- Map -->
        <div class="flex-1">
            <div id="command-map"></div>
        </div>
    </div>

    <!-- Store Inquiry Modal -->
    <div id="store-inquiry-modal" class="fixed inset-0 z-50 hidden" x-data="storeInquiry()">
        <div class="fixed inset-0 bg-black/50" @click="close()"></div>
        <div class="fixed inset-y-0 right-0 w-full max-w-lg bg-white shadow-xl overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-lg font-bold" x-text="data?.store?.name ?? 'Store Inquiry'"></h2>
                    <button @click="close()" class="text-gray-400 hover:text-gray-600 text-2xl leading-none">&times;</button>
                </div>
                <template x-if="loading">
                    <div class="flex justify-center py-12"><div class="animate-spin h-8 w-8 border-4 border-indigo-500 border-t-transparent rounded-full"></div></div>
                </template>
                <template x-if="!loading && data">
                    <div>
                        <div class="mb-4 p-3 bg-gray-50 rounded-lg">
                            <div class="text-sm"><span class="text-gray-500">Code:</span> <span x-text="data.store.code" class="font-medium"></span></div>
                            <div class="text-sm"><span class="text-gray-500">Address:</span> <span x-text="data.store.address || '-'" class="font-medium"></span></div>
                            <div class="text-sm"><span class="text-gray-500">Area:</span> <span x-text="data.store.area || '-'" class="font-medium"></span></div>
                        </div>
                        <template x-if="data.credit">
                            <div class="mb-4">
                                <h3 class="text-sm font-semibold text-gray-700 mb-2">Credit Account</h3>
                                <div class="grid grid-cols-3 gap-2 text-center">
                                    <div class="p-2 bg-blue-50 rounded"><div class="text-lg font-bold text-blue-700" x-text="'$' + Number(data.credit.credit_limit).toFixed(2)"></div><div class="text-xs text-gray-500">Limit</div></div>
                                    <div class="p-2 bg-red-50 rounded"><div class="text-lg font-bold text-red-700" x-text="'$' + Number(data.credit.current_balance).toFixed(2)"></div><div class="text-xs text-gray-500">Balance</div></div>
                                    <div class="p-2 bg-green-50 rounded"><div class="text-lg font-bold text-green-700" x-text="'$' + Number(data.credit.available_credit).toFixed(2)"></div><div class="text-xs text-gray-500">Available</div></div>
                                </div>
                            </div>
                        </template>
                        <template x-if="data.inventory && data.inventory.length > 0">
                            <div class="mb-4">
                                <h3 class="text-sm font-semibold text-gray-700 mb-2">Inventory (<span x-text="data.inventory.length"></span> products)</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full text-xs">
                                        <thead><tr class="bg-gray-50"><th class="px-2 py-1 text-left">Product</th><th class="px-2 py-1 text-right">Shelf</th><th class="px-2 py-1 text-right">Warehouse</th><th class="px-2 py-1 text-right">Total</th></tr></thead>
                                        <tbody>
                                            <template x-for="item in data.inventory" :key="item.sku">
                                                <tr class="border-b"><td class="px-2 py-1" x-text="item.product_name"></td><td class="px-2 py-1 text-right" x-text="item.on_shelf"></td><td class="px-2 py-1 text-right" x-text="item.warehouse"></td><td class="px-2 py-1 text-right font-medium" x-text="item.total"></td></tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </template>
                        <template x-if="data.recent_orders && data.recent_orders.length > 0">
                            <div class="mb-4">
                                <h3 class="text-sm font-semibold text-gray-700 mb-2">Recent Orders</h3>
                                <template x-for="order in data.recent_orders" :key="order.id">
                                    <div class="flex justify-between items-center p-2 border-b text-sm">
                                        <div>
                                            <span class="font-medium" x-text="order.order_number"></span>
                                            <span class="ml-2 text-xs px-1.5 py-0.5 rounded" :class="order.status === 'delivered' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'" x-text="order.status"></span>
                                        </div>
                                        <span class="font-medium" x-text="'$' + Number(order.total_amount).toFixed(2)"></span>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <template x-if="data.assigned_field_force && data.assigned_field_force.length > 0">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-700 mb-2">Assigned Sales Reps</h3>
                                <template x-for="u in data.assigned_field_force" :key="u.id">
                                    <div class="flex justify-between text-sm p-1 border-b"><span x-text="u.name"></span><span class="text-gray-500 text-xs" x-text="u.role"></span></div>
                                </template>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
        const fieldForceData = @json($fieldForce->values());
        const storeData = @json($stores->values());

        // ─── Store Inquiry ──────────────────────────────────────
        function storeInquiry() {
            return {
                loading: false,
                data: null,
                async open(storeId) {
                    this.loading = true;
                    this.data = null;
                    document.getElementById('store-inquiry-modal').classList.remove('hidden');
                    try {
                        const res = await fetch(`/command-center/stores/${storeId}/inquiry`);
                        const json = await res.json();
                        this.data = json.data;
                    } catch (e) { this.data = null; }
                    this.loading = false;
                },
                close() {
                    document.getElementById('store-inquiry-modal').classList.add('hidden');
                    this.data = null;
                }
            };
        }

        // ─── Main Command Center ────────────────────────────────
        function commandCenter() {
            return {
                map: null,
                searchUser: '',
                fieldForce: fieldForceData,
                stores: storeData,
                userMarkers: [],
                storeMarkers: [],
                trailPolyline: null,
                trailStartMarker: null,

                get filteredUsers() {
                    if (!this.searchUser) return this.fieldForce;
                    const q = this.searchUser.toLowerCase();
                    return this.fieldForce.filter(u =>
                        u.name.toLowerCase().includes(q) || u.role_label.toLowerCase().includes(q)
                    );
                },

                init() {
                    // Default center Riyadh; will auto-fit if data present
                    this.map = L.map('command-map').setView([24.7136, 46.6753], 11);
                    window._ccMap = this.map; // expose for showUserTrail()

                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap contributors',
                        maxZoom: 19,
                    }).addTo(this.map);

                    this.renderStoreMarkers();
                    this.renderUserMarkers();
                    this.fitBounds();

                    // Auto-refresh every 30 seconds
                    setInterval(() => this.refreshPositions(), 30000);
                },

                renderStoreMarkers() {
                    this.storeMarkers.forEach(m => m.remove());
                    this.storeMarkers = [];

                    this.stores.forEach(store => {
                        if (!store.latitude || !store.longitude) return;

                        const icon = L.divIcon({
                            html: `<div style="width:28px;height:28px;background:#8b5cf6;border:2px solid white;border-radius:5px;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 5px rgba(0,0,0,.35);cursor:pointer;"><svg width="14" height="14" viewBox="0 0 20 20" fill="white"><path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"/></svg></div>`,
                            className: '',
                            iconSize: [28, 28],
                            iconAnchor: [14, 14],
                            popupAnchor: [0, -16],
                        });

                        const popup = `<div class="store-popup">
                            <h4>${this.escapeHtml(store.name)}</h4>
                            <div class="stat-row"><span>Code</span><span>${store.code || '-'}</span></div>
                            <div class="stat-row"><span>Orders</span><span>${store.sales.order_count}</span></div>
                            <div class="stat-row"><span>Total Sales</span><span>$${Number(store.sales.total_sales).toFixed(2)}</span></div>
                            <div class="stat-row"><span>Stock Units</span><span>${store.inventory.total_stock}</span></div>
                            ${store.credit ? `<div class="stat-row"><span>Credit Available</span><span>$${Number(store.credit.available_credit).toFixed(2)}</span></div>` : ''}
                            <div style="margin-top:8px;text-align:center;">
                                <button onclick="openStoreInquiry(${store.id})" style="background:#4f46e5;color:white;padding:4px 14px;border-radius:4px;font-size:12px;border:none;cursor:pointer;">View Details</button>
                            </div>
                        </div>`;

                        const marker = L.marker([store.latitude, store.longitude], { icon })
                            .bindPopup(popup)
                            .addTo(this.map);
                        this.storeMarkers.push(marker);
                    });
                },

                renderUserMarkers() {
                    this.userMarkers.forEach(m => m.remove());
                    this.userMarkers = [];

                    this.fieldForce.forEach(user => {
                        if (!user.latitude || !user.longitude) return;

                        const color = user.is_online ? '#10b981' : '#9ca3af';
                        const pulse = user.is_online ? `box-shadow:0 0 0 6px rgba(16,185,129,.25);animation:pulse 2s infinite;` : '';
                        const icon = L.divIcon({
                            html: `<div style="width:34px;height:34px;background:${color};border:3px solid white;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 6px rgba(0,0,0,.4);cursor:pointer;${pulse}"><svg width="17" height="17" viewBox="0 0 20 20" fill="white"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg></div>`,
                            className: '',
                            iconSize: [34, 34],
                            iconAnchor: [17, 17],
                            popupAnchor: [0, -20],
                        });

                        const lastSeen = user.last_seen
                            ? `<div style="font-size:11px;color:#9ca3af;margin-top:4px;">Last seen: ${new Date(user.last_seen).toLocaleTimeString()}</div>`
                            : '';
                        const speed = user.speed_kmh
                            ? `<div style="font-size:12px;">Speed: ${Number(user.speed_kmh).toFixed(1)} km/h</div>`
                            : '';
                        const popup = `<div class="user-popup">
                            <h4>${this.escapeHtml(user.name)}</h4>
                            <div style="font-size:12px;color:#6b7280;">${user.role_label}</div>
                            <div style="font-size:12px;margin-top:4px;"><span style="color:${color};">●</span> ${user.is_online ? 'Online' : 'Offline'}</div>
                            ${speed}${lastSeen}
                            <div style="margin-top:8px;text-align:center;">
                                <button onclick="showUserTrail(${user.id})" style="background:#059669;color:white;padding:4px 12px;border-radius:4px;font-size:12px;border:none;cursor:pointer;">View Trail</button>
                            </div>
                        </div>`;

                        const marker = L.marker([user.latitude, user.longitude], { icon })
                            .bindPopup(popup)
                            .addTo(this.map);
                        this.userMarkers.push(marker);
                    });
                },

                focusUser(user) {
                    if (!user.latitude || !user.longitude) return;
                    this.map.setView([user.latitude, user.longitude], 16);
                },

                fitBounds() {
                    const coords = [];
                    this.fieldForce.forEach(u => { if (u.latitude && u.longitude) coords.push([u.latitude, u.longitude]); });
                    this.stores.forEach(s => { if (s.latitude && s.longitude) coords.push([s.latitude, s.longitude]); });
                    if (coords.length === 0) return;
                    this.map.fitBounds(L.latLngBounds(coords), { padding: [60, 60] });
                },

                async refreshPositions() {
                    try {
                        const res = await fetch('/command-center/field-force-json');
                        const json = await res.json();
                        this.fieldForce = json.data;
                        this.renderUserMarkers();
                    } catch (e) { /* silent */ }
                },

                escapeHtml(text) {
                    const div = document.createElement('div');
                    div.appendChild(document.createTextNode(text));
                    return div.innerHTML;
                },
            };
        }

        // ─── Popups opened from inside Leaflet popup HTML ───────
        function openStoreInquiry(storeId) {
            // Find the Alpine component for the modal
            const modal = document.getElementById('store-inquiry-modal');
            if (modal && modal._x_dataStack) {
                modal._x_dataStack[0].open(storeId);
            }
        }

        async function showUserTrail(userId) {
            try {
                const res = await fetch(`/command-center/users/${userId}/activity`);
                const json = await res.json();
                const data = json.data;
                if (!data || !data.gps_trail || data.gps_trail.length === 0) return;

                const map = window._ccMap;
                if (!map) return;

                // Remove existing trail
                if (window._ccTrail) { window._ccTrail.remove(); window._ccTrail = null; }
                if (window._ccTrailStart) { window._ccTrailStart.remove(); window._ccTrailStart = null; }

                const coords = data.gps_trail.map(p => [p.latitude, p.longitude]);

                window._ccTrail = L.polyline(coords, {
                    color: '#3b82f6',
                    weight: 3,
                    dashArray: '6 4',
                    opacity: 0.85,
                }).addTo(map);

                // Start marker
                window._ccTrailStart = L.circleMarker(coords[0], {
                    radius: 7,
                    fillColor: '#10b981',
                    color: 'white',
                    weight: 2,
                    fillOpacity: 1,
                }).bindTooltip('Start').addTo(map);

                map.fitBounds(window._ccTrail.getBounds(), { padding: [60, 60] });
            } catch (e) { console.error('Failed to load user trail', e); }
        }

        // Store the Leaflet map reference on the DOM element for showUserTrail access
        document.addEventListener('DOMContentLoaded', function () {
            // nothing needed — _ccMap is set during Alpine init()
        });
    </script>
    @endpush
</x-layouts.app>