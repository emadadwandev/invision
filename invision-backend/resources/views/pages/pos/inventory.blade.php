<x-layouts.app title="Store Inventory">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Store Inventory</h1>
    </div>

    <form method="GET" action="{{ route('pos.inventory') }}" class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search product..."
            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        <select name="store_id" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="">All Stores</option>
            @foreach($stores as $store)
                <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
            @endforeach
        </select>
        <input type="number" name="low_stock" value="{{ request('low_stock') }}" placeholder="Low stock threshold..."
            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        <button type="submit" class="inline-flex items-center justify-center rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
            Filter
        </button>
    </form>

    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Store</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">On Shelf</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Warehouse</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Last Count</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($inventory as $inv)
                <tr>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">
                        {{ $inv->product?->name }}
                        <span class="text-xs text-gray-500 block">{{ $inv->product?->sku }}</span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $inv->store?->name }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-right text-gray-900">{{ $inv->on_shelf_quantity }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-right text-gray-900">{{ $inv->warehouse_quantity }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-right font-medium {{ ($inv->on_shelf_quantity + $inv->warehouse_quantity) < 10 ? 'text-red-600' : 'text-gray-900' }}">
                        {{ $inv->on_shelf_quantity + $inv->warehouse_quantity }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $inv->last_counted_at?->diffForHumans() ?? 'Never' }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm" x-data="{ open: false }">
                        <button @click="open = !open" class="text-indigo-600 hover:text-indigo-900 text-sm">Update Count</button>
                        <div x-show="open" @click.outside="open = false" class="mt-2 absolute right-8 bg-white border rounded-lg shadow-lg p-4 z-10">
                            <form method="POST" action="{{ route('pos.inventory-update', $inv) }}" class="space-y-2">
                                @csrf @method('PUT')
                                <input type="number" name="on_shelf_quantity" value="{{ $inv->on_shelf_quantity }}" placeholder="On Shelf"
                                    class="block w-32 rounded-md border-gray-300 shadow-sm sm:text-sm">
                                <input type="number" name="warehouse_quantity" value="{{ $inv->warehouse_quantity }}" placeholder="Warehouse"
                                    class="block w-32 rounded-md border-gray-300 shadow-sm sm:text-sm">
                                <button type="submit" class="w-full rounded-md bg-indigo-600 px-3 py-1 text-sm text-white hover:bg-indigo-500">Save</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">No inventory records found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $inventory->links() }}</div>
</x-layouts.app>
