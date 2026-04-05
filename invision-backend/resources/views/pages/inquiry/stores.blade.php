<x-layouts.app title="Store Inquiry">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Store Inquiry</h1>
        <p class="mt-1 text-sm text-gray-600">Detailed store performance with sales, stock and credit data.</p>
    </div>

    {{-- Filters --}}
    <div class="bg-white shadow rounded-lg p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-5">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Name or code..."
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Category</label>
                <select name="category" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Categories</option>
                    @foreach (\App\Enums\StoreCategory::cases() as $cat)
                        <option value="{{ $cat->value }}" @selected(($filters['category'] ?? '') === $cat->value)>{{ $cat->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Rank</label>
                <select name="rank" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Ranks</option>
                    @foreach (\App\Enums\StoreRank::cases() as $rank)
                        <option value="{{ $rank->value }}" @selected(($filters['rank'] ?? '') === $rank->value)>{{ $rank->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Area</label>
                <select name="area_id" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Areas</option>
                    @foreach (\App\Models\Area::orderBy('name')->get() as $area)
                        <option value="{{ $area->id }}" @selected(($filters['area_id'] ?? '') == $area->id)>{{ $area->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">Filter</button>
                <a href="{{ route('inquiry.stores') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300">Reset</a>
            </div>
        </form>
    </div>

    {{-- Results Table --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200 flex items-center justify-between">
            <span class="text-sm text-gray-600">{{ $stores->count() }} stores found</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Store Name</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Area</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Orders</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total Sales</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Stock</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Credit Limit</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($stores as $store)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-500 font-mono">{{ $store['code'] }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $store['name'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $store['category'] ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $store['rank'] ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $store['area'] ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600">{{ number_format($store['order_count']) }}</td>
                        <td class="px-4 py-3 text-sm text-right font-medium text-green-600">${{ number_format($store['total_sales'], 2) }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600">{{ number_format($store['stock_quantity']) }}</td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600">{{ $store['credit_limit'] !== null ? '$'.number_format($store['credit_limit'], 2) : '-' }}</td>
                        <td class="px-4 py-3 text-sm text-right {{ ($store['credit_balance'] ?? 0) > 0 ? 'text-red-600 font-medium' : 'text-gray-600' }}">{{ $store['credit_balance'] !== null ? '$'.number_format($store['credit_balance'], 2) : '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="px-4 py-8 text-center text-sm text-gray-400">No stores found matching your criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
