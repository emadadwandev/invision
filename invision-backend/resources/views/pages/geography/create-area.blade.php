<x-layouts.app title="Add Area">
    <div class="mb-6">
        <a href="{{ route('geography.index', ['tab' => 'areas']) }}" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Back to Geography</a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Add Area</h1>
    </div>

    <form method="POST" action="{{ route('geography.areas.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Area Details</h2>
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Area Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('name') border-red-500 @enderror">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="sector_id" class="block text-sm font-medium text-gray-700">Sector *</label>
                    <select name="sector_id" id="sector_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('sector_id') border-red-500 @enderror">
                        <option value="">Select sector...</option>
                        @foreach($sectors as $sector)
                            <option value="{{ $sector->id }}" {{ old('sector_id') == $sector->id ? 'selected' : '' }}>{{ $sector->name }}</option>
                        @endforeach
                    </select>
                    @error('sector_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="radius_meters" class="block text-sm font-medium text-gray-700">Radius (meters)</label>
                    <input type="number" name="radius_meters" id="radius_meters" value="{{ old('radius_meters') }}" min="1"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('radius_meters') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- Map Picker --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-2">Area Center Location</h2>
            <p class="text-sm text-gray-500 mb-4">Click on the map to set the center GPS coordinates for this area.</p>
            <div id="area-map" class="w-full h-96 rounded-lg border border-gray-300 mb-4"></div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="gps_latitude" class="block text-sm font-medium text-gray-700">Latitude</label>
                    <input type="number" step="0.00000001" name="gps_latitude" id="gps_latitude" value="{{ old('gps_latitude') }}" readonly
                           class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm">
                </div>
                <div>
                    <label for="gps_longitude" class="block text-sm font-medium text-gray-700">Longitude</label>
                    <input type="number" step="0.00000001" name="gps_longitude" id="gps_longitude" value="{{ old('gps_longitude') }}" readonly
                           class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm">
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('geography.index', ['tab' => 'areas']) }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Create Area</button>
        </div>
    </form>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        const map = L.map('area-map').setView([24.7136, 46.6753], 10);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);

        let marker = null;
        const latInput = document.getElementById('gps_latitude');
        const lngInput = document.getElementById('gps_longitude');

        if (latInput.value && lngInput.value) {
            const lat = parseFloat(latInput.value);
            const lng = parseFloat(lngInput.value);
            marker = L.marker([lat, lng]).addTo(map);
            map.setView([lat, lng], 14);
        }

        map.on('click', function (e) {
            const { lat, lng } = e.latlng;
            latInput.value = lat.toFixed(8);
            lngInput.value = lng.toFixed(8);

            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng).addTo(map);
            }
        });
    });
    </script>
    @endpush
</x-layouts.app>
