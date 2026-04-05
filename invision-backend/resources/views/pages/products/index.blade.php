<x-layouts.app title="Products">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Products</h1>
            <p class="mt-1 text-sm text-gray-600">Manage product catalog, SKUs, and pricing.</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-3">
            @can('create', App\Models\Product::class)
            <a href="{{ route('products.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                + Add Product
            </a>
            @endcan
            <a href="{{ route('product-categories.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50">
                Categories
            </a>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg mb-6 p-4">
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name, SKU, or barcode..."
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div class="min-w-[180px]">
                <label class="block text-sm font-medium text-gray-700">Category</label>
                <select name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">All</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm hover:bg-gray-700">Filter</button>
            <a href="{{ route('products.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-50">Reset</a>
        </form>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Barcode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($products as $product)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('products.show', $product) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600">{{ $product->name }}</a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->sku ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->barcode ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $product->category?->name ?? '—' }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $product->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('products.edit', $product) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No products found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($products->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">{{ $products->withQueryString()->links() }}</div>
        @endif
    </div>
</x-layouts.app>
