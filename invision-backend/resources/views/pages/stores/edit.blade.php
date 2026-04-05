<x-layouts.app title="Edit Store">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Store</h1>
        <p class="mt-1 text-sm text-gray-600">Update store information.</p>
    </div>

    <form method="POST" action="{{ route('stores.update', $store) }}" class="space-y-6">
        @csrf @method('PUT')

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Store Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name *</label>
                    <input type="text" name="name" value="{{ old('name', $store->name) }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Code</label>
                    <input type="text" name="code" value="{{ old('code', $store->code) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('code') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Category *</label>
                    <select name="category" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @foreach(App\Enums\StoreCategory::cases() as $cat)
                            <option value="{{ $cat->value }}" {{ old('category', $store->category->value) === $cat->value ? 'selected' : '' }}>{{ $cat->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Rank</label>
                    <select name="rank" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @foreach(App\Enums\StoreRank::cases() as $rank)
                            <option value="{{ $rank->value }}" {{ old('rank', $store->rank->value) === $rank->value ? 'selected' : '' }}>{{ $rank->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Area</label>
                    <select name="area_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">— None —</option>
                        @foreach($areas as $area)
                            <option value="{{ $area->id }}" {{ old('area_id', $store->area_id) == $area->id ? 'selected' : '' }}>{{ $area->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Address</label>
                    <input type="text" name="address" value="{{ old('address', $store->address) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div class="flex items-center gap-2 mt-6">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $store->is_active) ? 'checked' : '' }}
                           class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <label class="text-sm font-medium text-gray-700">Active</label>
                </div>
            </div>
        </div>

        {{-- Map Location Picker --}}
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-2">Store Location</h2>
            <p class="text-sm text-gray-500 mb-4">Click on the map to change the store's GPS coordinates, or drag the marker to adjust.</p>
            <div id="store-map" class="w-full h-96 rounded-lg border border-gray-300 mb-4"></div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="gps_latitude" class="block text-sm font-medium text-gray-700">Latitude</label>
                    <input type="number" step="0.00000001" name="gps_latitude" id="gps_latitude" value="{{ old('gps_latitude', $store->gps_latitude) }}" readonly
                           class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm">
                </div>
                <div>
                    <label for="gps_longitude" class="block text-sm font-medium text-gray-700">Longitude</label>
                    <input type="number" step="0.00000001" name="gps_longitude" id="gps_longitude" value="{{ old('gps_longitude', $store->gps_longitude) }}" readonly
                           class="mt-1 block w-full rounded-md border-gray-300 bg-gray-50 shadow-sm sm:text-sm">
                </div>
            </div>
        </div>

        {{-- Store Contacts --}}
        <div class="bg-white shadow rounded-lg p-6" x-data="contactsManager()">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-lg font-medium text-gray-900">Store Contacts</h2>
                    <p class="text-sm text-gray-500 mt-1">Add one or more contacts. Mark one as the primary contact.</p>
                </div>
                <button type="button" @click="addContact()"
                        class="inline-flex items-center px-3 py-1.5 border border-indigo-300 text-indigo-700 bg-indigo-50 rounded-md text-sm font-medium hover:bg-indigo-100">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Contact
                </button>
            </div>

            @error('contacts') <p class="text-red-500 text-xs mb-3">{{ $message }}</p> @enderror

            <template x-if="contacts.length === 0">
                <p class="text-sm text-gray-400 italic py-4 text-center">No contacts added yet. Click "Add Contact" to start.</p>
            </template>

            <template x-for="(contact, index) in contacts" :key="index">
                <div class="border border-gray-200 rounded-lg p-4 mb-4 relative" :class="contact.is_primary ? 'ring-2 ring-indigo-500 bg-indigo-50/30' : ''">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-semibold text-gray-700" x-text="'Contact #' + (index + 1)"></span>
                        <div class="flex items-center gap-3">
                            <label class="inline-flex items-center gap-1.5 cursor-pointer">
                                <input type="radio" :name="'contacts[' + index + '][is_primary]'" :value="1"
                                       :checked="contact.is_primary"
                                       @change="setPrimary(index)"
                                       class="text-indigo-600 focus:ring-indigo-500">
                                <span class="text-xs font-medium" :class="contact.is_primary ? 'text-indigo-700' : 'text-gray-500'">Primary</span>
                            </label>
                            <button type="button" @click="removeContact(index)" class="text-red-400 hover:text-red-600" title="Remove contact">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                    <input type="hidden" :name="'contacts[' + index + '][is_primary]'" :value="contact.is_primary ? 1 : 0">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Name *</label>
                            <input type="text" :name="'contacts[' + index + '][name]'" x-model="contact.name" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Position / Type *</label>
                            <select :name="'contacts[' + index + '][position]'" x-model="contact.position" required
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">— Select Type —</option>
                                @foreach(App\Enums\ContactType::cases() as $type)
                                    <option value="{{ $type->value }}">{{ $type->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Phone *</label>
                            <input type="tel" :name="'contacts[' + index + '][phone]'" x-model="contact.phone" required
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Email</label>
                            <input type="email" :name="'contacts[' + index + '][email]'" x-model="contact.email"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>
                </div>
            </template>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('stores.show', $store) }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Update Store</button>
        </div>
    </form>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
    <script>
    function contactsManager() {
        @php
            $existingContacts = old('contacts', $store->contacts->map(fn ($c) => [
                'name' => $c->name,
                'phone' => $c->phone,
                'email' => $c->email,
                'position' => $c->position,
                'is_primary' => $c->is_primary,
            ])->values()->toArray());
        @endphp
        const existing = @json($existingContacts);
        return {
            contacts: existing,
            addContact() {
                this.contacts.push({ name: '', phone: '', email: '', position: '', is_primary: this.contacts.length === 0 });
            },
            removeContact(index) {
                const wasPrimary = this.contacts[index].is_primary;
                this.contacts.splice(index, 1);
                if (wasPrimary && this.contacts.length > 0) {
                    this.contacts[0].is_primary = true;
                }
            },
            setPrimary(index) {
                this.contacts.forEach((c, i) => c.is_primary = (i === index));
            }
        };
    }

    document.addEventListener('DOMContentLoaded', function () {
        const defaultLat = {{ $store->gps_latitude ?? 24.7136 }};
        const defaultLng = {{ $store->gps_longitude ?? 46.6753 }};
        const hasCoords = {{ ($store->gps_latitude && $store->gps_longitude) ? 'true' : 'false' }};
        const zoom = hasCoords ? 16 : 10;

        const map = L.map('store-map').setView([defaultLat, defaultLng], zoom);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; OpenStreetMap contributors',
            maxZoom: 19,
        }).addTo(map);

        const latInput = document.getElementById('gps_latitude');
        const lngInput = document.getElementById('gps_longitude');

        let marker = null;
        if (hasCoords) {
            marker = L.marker([defaultLat, defaultLng], { draggable: true }).addTo(map);
            marker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                latInput.value = pos.lat.toFixed(8);
                lngInput.value = pos.lng.toFixed(8);
            });
        }

        map.on('click', function (e) {
            const { lat, lng } = e.latlng;
            latInput.value = lat.toFixed(8);
            lngInput.value = lng.toFixed(8);

            if (marker) {
                marker.setLatLng(e.latlng);
            } else {
                marker = L.marker(e.latlng, { draggable: true }).addTo(map);
                marker.on('dragend', function (ev) {
                    const pos = ev.target.getLatLng();
                    latInput.value = pos.lat.toFixed(8);
                    lngInput.value = pos.lng.toFixed(8);
                });
            }
        });
    });
    </script>
    @endpush
</x-layouts.app>
