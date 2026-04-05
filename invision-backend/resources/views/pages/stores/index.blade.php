<x-layouts.app title="Stores">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Stores</h1>
            <p class="mt-1 text-sm text-gray-600">Manage store database and profiles.</p>
        </div>
        @can('create', App\Models\Store::class)
        <a href="{{ route('stores.create') }}" class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
            + Add Store
        </a>
        @endcan
    </div>

    <div class="bg-white shadow rounded-lg mb-6 p-4">
        <form method="GET" action="{{ route('stores.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Store name or code..."
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div class="min-w-[160px]">
                <label class="block text-sm font-medium text-gray-700">Category</label>
                <select name="category" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">All</option>
                    @foreach(App\Enums\StoreCategory::cases() as $cat)
                        <option value="{{ $cat->value }}" {{ request('category') === $cat->value ? 'selected' : '' }}>{{ $cat->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[120px]">
                <label class="block text-sm font-medium text-gray-700">Rank</label>
                <select name="rank" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">All</option>
                    @foreach(App\Enums\StoreRank::cases() as $rank)
                        <option value="{{ $rank->value }}" {{ request('rank') === $rank->value ? 'selected' : '' }}>{{ $rank->label() }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm hover:bg-gray-700">Filter</button>
            <a href="{{ route('stores.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-50">Reset</a>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rank</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Area</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($stores as $store)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('stores.show', $store) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600">{{ $store->name }}</a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $store->code ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $store->category->label() }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $store->rank->label() }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $store->area?->name ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $store->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $store->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('stores.edit', $store) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">No stores found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($stores->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">{{ $stores->withQueryString()->links() }}</div>
        @endif
    </div>
</x-layouts.app>
