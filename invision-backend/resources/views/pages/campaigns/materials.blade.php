<x-layouts.app title="POSM Materials">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">POSM Materials</h1>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('campaigns.materials') }}" class="bg-white shadow rounded-lg p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Name or SKU..."
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">Search</button>
            </div>
            <div class="flex items-end">
                <a href="{{ route('campaigns.materials') }}" class="w-full text-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">Reset</a>
            </div>
        </div>
    </form>

    {{-- Materials Table --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Available</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($materials as $material)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $material->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $material->type ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ $material->sku ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $material->quantity_available ?? 0 }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $material->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $material->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('campaigns.material-show', $material) }}" class="text-sm text-indigo-600 hover:text-indigo-900">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">No POSM materials found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $materials->withQueryString()->links() }}
    </div>
</x-layouts.app>
