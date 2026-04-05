<x-layouts.app :title="'Geo-Fence Settings'">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Geo-Fence Settings</h1>
        <a href="{{ route('geofence.duty-sessions') }}"
           class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
            Duty Sessions
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-md bg-green-50 p-4">
            <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
        </div>
    @endif

    <div class="bg-white shadow rounded-lg p-6">
        <form method="POST" action="{{ route('geofence.settings.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Check-in Radius --}}
                <div>
                    <label for="checkin_radius_meters" class="block text-sm font-medium text-gray-700">Check-in Radius (meters)</label>
                    <input type="number" name="checkin_radius_meters" id="checkin_radius_meters"
                           value="{{ old('checkin_radius_meters', $settings->checkin_radius_meters) }}"
                           min="5" max="1000"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">Maximum distance (in meters) for a valid check-in. Default: 50m</p>
                    @error('checkin_radius_meters') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Check-out Radius --}}
                <div>
                    <label for="checkout_radius_meters" class="block text-sm font-medium text-gray-700">Check-out Radius (meters)</label>
                    <input type="number" name="checkout_radius_meters" id="checkout_radius_meters"
                           value="{{ old('checkout_radius_meters', $settings->checkout_radius_meters) }}"
                           min="5" max="2000"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">Maximum distance for a valid check-out. Default: 100m</p>
                    @error('checkout_radius_meters') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- GPS Tracking Interval --}}
                <div>
                    <label for="gps_tracking_interval_seconds" class="block text-sm font-medium text-gray-700">GPS Tracking Interval (seconds)</label>
                    <input type="number" name="gps_tracking_interval_seconds" id="gps_tracking_interval_seconds"
                           value="{{ old('gps_tracking_interval_seconds', $settings->gps_tracking_interval_seconds) }}"
                           min="5" max="300"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">How often the mobile app sends GPS coordinates. Default: 30s</p>
                    @error('gps_tracking_interval_seconds') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Batch Size --}}
                <div>
                    <label for="gps_batch_size" class="block text-sm font-medium text-gray-700">GPS Batch Size</label>
                    <input type="number" name="gps_batch_size" id="gps_batch_size"
                           value="{{ old('gps_batch_size', $settings->gps_batch_size) }}"
                           min="1" max="100"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">Number of GPS logs to batch before sending. Default: 10</p>
                    @error('gps_batch_size') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                {{-- Auto-Checkout Distance --}}
                <div>
                    <label for="auto_checkout_distance_meters" class="block text-sm font-medium text-gray-700">Auto-Checkout Distance (meters)</label>
                    <input type="number" name="auto_checkout_distance_meters" id="auto_checkout_distance_meters"
                           value="{{ old('auto_checkout_distance_meters', $settings->auto_checkout_distance_meters) }}"
                           min="50" max="5000"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <p class="mt-1 text-xs text-gray-500">Distance from store that triggers auto-checkout. Default: 200m</p>
                    @error('auto_checkout_distance_meters') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Toggle Settings --}}
            <div class="space-y-4 pt-4 border-t">
                <h3 class="text-lg font-medium text-gray-900">Enforcement Options</h3>

                <label class="flex items-center gap-3">
                    <input type="hidden" name="enforce_geofence" value="0">
                    <input type="checkbox" name="enforce_geofence" value="1"
                           {{ $settings->enforce_geofence ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <div>
                        <span class="text-sm font-medium text-gray-700">Enforce Geo-Fence</span>
                        <p class="text-xs text-gray-500">Reject check-ins outside the radius</p>
                    </div>
                </label>

                <label class="flex items-center gap-3">
                    <input type="hidden" name="require_gps_for_checkin" value="0">
                    <input type="checkbox" name="require_gps_for_checkin" value="1"
                           {{ $settings->require_gps_for_checkin ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <div>
                        <span class="text-sm font-medium text-gray-700">Require GPS for Check-in</span>
                        <p class="text-xs text-gray-500">Require GPS coordinates for store check-ins</p>
                    </div>
                </label>

                <label class="flex items-center gap-3">
                    <input type="hidden" name="auto_checkout_on_leave" value="0">
                    <input type="checkbox" name="auto_checkout_on_leave" value="1"
                           {{ $settings->auto_checkout_on_leave ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500">
                    <div>
                        <span class="text-sm font-medium text-gray-700">Auto-Checkout on Leave</span>
                        <p class="text-xs text-gray-500">Automatically check-out when user leaves the store area</p>
                    </div>
                </label>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit"
                        class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                    Save Settings
                </button>
            </div>
        </form>
    </div>
</x-layouts.app>
