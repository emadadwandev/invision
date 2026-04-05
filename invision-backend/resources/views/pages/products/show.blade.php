<x-layouts.app title="{{ $product->name }}">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $product->name }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $product->sku ? "SKU: {$product->sku}" : 'Product Details' }}</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-3">
            @can('update', $product)
            <a href="{{ route('products.edit', $product) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Edit</a>
            @endcan
            <a href="{{ route('products.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-50">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Product Information</h2>
            <dl class="space-y-3">
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Category</dt><dd class="text-sm font-medium text-gray-900">{{ $product->category?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-gray-500">SKU</dt><dd class="text-sm font-medium text-gray-900">{{ $product->sku ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Barcode</dt><dd class="text-sm font-medium text-gray-900">{{ $product->barcode ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Status</dt><dd><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span></dd></div>
            </dl>
            @if($product->description)
            <div class="mt-4 pt-4 border-t border-gray-200">
                <dt class="text-sm text-gray-500 mb-1">Description</dt>
                <dd class="text-sm text-gray-900">{{ $product->description }}</dd>
            </div>
            @endif
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Price Levels</h2>
            @if($product->priceLevels->isEmpty())
                <p class="text-sm text-gray-500">No price levels defined.</p>
            @else
                <table class="min-w-full divide-y divide-gray-200">
                    <thead><tr>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase pb-2">Level</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase pb-2">Price</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase pb-2">From</th>
                        <th class="text-left text-xs font-medium text-gray-500 uppercase pb-2">To</th>
                    </tr></thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($product->priceLevels as $level)
                        <tr>
                            <td class="py-2 text-sm text-gray-900">{{ $level->level_name }}</td>
                            <td class="py-2 text-sm text-gray-900">{{ number_format($level->price, 2) }}</td>
                            <td class="py-2 text-sm text-gray-500">{{ $level->effective_from->format('M d, Y') }}</td>
                            <td class="py-2 text-sm text-gray-500">{{ $level->effective_to?->format('M d, Y') ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>

    <div class="mt-6 bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Assigned Stores</h2>
        @if($product->stores->isEmpty())
            <p class="text-sm text-gray-500">Not assigned to any stores.</p>
        @else
            <div class="flex flex-wrap gap-2">
                @foreach($product->stores as $store)
                <a href="{{ route('stores.show', $store) }}" class="inline-flex items-center px-3 py-1 rounded-full text-sm bg-gray-100 text-gray-800 hover:bg-gray-200">{{ $store->name }}</a>
                @endforeach
            </div>
        @endif
    </div>
</x-layouts.app>
