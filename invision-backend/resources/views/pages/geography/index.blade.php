<x-layouts.app title="Geography Management">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Geography Management</h1>
            <p class="mt-1 text-sm text-gray-600">Manage cities, districts, sectors (zones), and areas.</p>
        </div>
    </div>

    @if(session('success'))
    <div class="mb-4 bg-green-50 border border-green-200 rounded-md p-4">
        <p class="text-sm text-green-800">{{ session('success') }}</p>
    </div>
    @endif

    {{-- Tabs --}}
    <div x-data="{ activeTab: '{{ $tab }}' }" class="space-y-6">
        <div class="border-b border-gray-200">
            <nav class="-mb-px flex space-x-8">
                <button @click="activeTab = 'cities'" :class="activeTab === 'cities' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                    Cities <span class="ml-1 text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ $cities->total() }}</span>
                </button>
                <button @click="activeTab = 'districts'" :class="activeTab === 'districts' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                    Districts <span class="ml-1 text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ $districts->total() }}</span>
                </button>
                <button @click="activeTab = 'sectors'" :class="activeTab === 'sectors' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                    Sectors (Zones) <span class="ml-1 text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ $sectors->total() }}</span>
                </button>
                <button @click="activeTab = 'areas'" :class="activeTab === 'areas' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'" class="whitespace-nowrap py-3 px-1 border-b-2 font-medium text-sm">
                    Areas <span class="ml-1 text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full">{{ $areas->total() }}</span>
                </button>
            </nav>
        </div>

        {{-- Cities Tab --}}
        <div x-show="activeTab === 'cities'" x-cloak>
            <div class="flex justify-end mb-4">
                <a href="{{ route('geography.cities.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    + Add City
                </a>
            </div>
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Country</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($cities as $city)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $city->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $city->country->name ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $city->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $city->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <form method="POST" action="{{ route('geography.cities.update', $city) }}" class="inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="name" value="{{ $city->name }}">
                                    <input type="hidden" name="is_active" value="{{ $city->is_active ? '0' : '1' }}">
                                    <button type="submit" class="text-yellow-600 hover:text-yellow-900 mr-3">{{ $city->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                                <form method="POST" action="{{ route('geography.cities.destroy', $city) }}" class="inline" onsubmit="return confirm('Delete this city?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">No cities found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if($cities->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">{{ $cities->appends(['tab' => 'cities'])->links() }}</div>
                @endif
            </div>
        </div>

        {{-- Districts Tab --}}
        <div x-show="activeTab === 'districts'" x-cloak>
            <div class="flex justify-end mb-4">
                <a href="{{ route('geography.districts.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    + Add District
                </a>
            </div>
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">City</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($districts as $district)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $district->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $district->city->name ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $district->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $district->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <form method="POST" action="{{ route('geography.districts.update', $district) }}" class="inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="name" value="{{ $district->name }}">
                                    <input type="hidden" name="is_active" value="{{ $district->is_active ? '0' : '1' }}">
                                    <button type="submit" class="text-yellow-600 hover:text-yellow-900 mr-3">{{ $district->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                                <form method="POST" action="{{ route('geography.districts.destroy', $district) }}" class="inline" onsubmit="return confirm('Delete this district?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">No districts found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if($districts->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">{{ $districts->appends(['tab' => 'districts'])->links() }}</div>
                @endif
            </div>
        </div>

        {{-- Sectors (Zones) Tab --}}
        <div x-show="activeTab === 'sectors'" x-cloak>
            <div class="flex justify-end mb-4">
                <a href="{{ route('geography.sectors.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    + Add Sector / Zone
                </a>
            </div>
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">District</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($sectors as $sector)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $sector->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $sector->district->name ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $sector->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $sector->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <form method="POST" action="{{ route('geography.sectors.update', $sector) }}" class="inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="name" value="{{ $sector->name }}">
                                    <input type="hidden" name="is_active" value="{{ $sector->is_active ? '0' : '1' }}">
                                    <button type="submit" class="text-yellow-600 hover:text-yellow-900 mr-3">{{ $sector->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                                <form method="POST" action="{{ route('geography.sectors.destroy', $sector) }}" class="inline" onsubmit="return confirm('Delete this sector?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">No sectors found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if($sectors->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">{{ $sectors->appends(['tab' => 'sectors'])->links() }}</div>
                @endif
            </div>
        </div>

        {{-- Areas Tab --}}
        <div x-show="activeTab === 'areas'" x-cloak>
            <div class="flex justify-end mb-4">
                <a href="{{ route('geography.areas.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                    + Add Area
                </a>
            </div>
            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sector</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">GPS</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Radius</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($areas as $area)
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $area->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $area->sector->name ?? '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                @if($area->gps_latitude && $area->gps_longitude)
                                    {{ number_format($area->gps_latitude, 5) }}, {{ number_format($area->gps_longitude, 5) }}
                                @else
                                    —
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $area->radius_meters ? $area->radius_meters . 'm' : '—' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $area->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $area->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                                <form method="POST" action="{{ route('geography.areas.update', $area) }}" class="inline">
                                    @csrf @method('PUT')
                                    <input type="hidden" name="name" value="{{ $area->name }}">
                                    <input type="hidden" name="is_active" value="{{ $area->is_active ? '0' : '1' }}">
                                    <button type="submit" class="text-yellow-600 hover:text-yellow-900 mr-3">{{ $area->is_active ? 'Deactivate' : 'Activate' }}</button>
                                </form>
                                <form method="POST" action="{{ route('geography.areas.destroy', $area) }}" class="inline" onsubmit="return confirm('Delete this area?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">No areas found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                @if($areas->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">{{ $areas->appends(['tab' => 'areas'])->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
