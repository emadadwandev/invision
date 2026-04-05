<x-layouts.app :title="'Competitor Products'">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('competitors.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Back to Competitors</a>
            <h1 class="text-2xl font-semibold text-gray-900 mt-1">Competitor Products</h1>
        </div>
        <a href="{{ route('competitors.products.create') }}"
           class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            + Add Product
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mb-6 flex gap-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products..."
               class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm w-64">
        <select name="competitor_id" onchange="this.form.submit()" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="">All Competitors</option>
            @foreach ($competitors as $comp)
                <option value="{{ $comp->id }}" {{ request('competitor_id') == $comp->id ? 'selected' : '' }}>{{ $comp->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            Search
        </button>
    </form>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Competitor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barcode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($products as $product)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $product->competitor?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $product->sku ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $product->barcode ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $product->category ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No competitor products found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $products->withQueryString()->links() }}
    </div>
</x-layouts.app>
