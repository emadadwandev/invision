<x-layouts.app :title="$competitor->name">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('competitors.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Back to Competitors</a>
            <h1 class="text-2xl font-semibold text-gray-900 mt-1">{{ $competitor->name }}</h1>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('competitors.edit', $competitor) }}"
               class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Edit
            </a>
        </div>
    </div>

    {{-- Overview Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Status</p>
            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $competitor->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                {{ $competitor->is_active ? 'Active' : 'Inactive' }}
            </span>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Products</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $competitor->products_count ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Observations</p>
            <p class="text-2xl font-semibold text-gray-900">{{ $competitor->observations_count ?? 0 }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-4">
            <p class="text-sm text-gray-500">Added</p>
            <p class="text-sm font-medium text-gray-900">{{ $competitor->created_at->format('M d, Y') }}</p>
        </div>
    </div>

    @if ($competitor->description)
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <h3 class="text-sm font-medium text-gray-500 mb-1">Description</h3>
            <p class="text-sm text-gray-900">{{ $competitor->description }}</p>
        </div>
    @endif

    {{-- Products --}}
    <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Products ({{ $competitor->products->count() }})</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Barcode</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($competitor->products as $product)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $product->name }}</td>
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
                        <td colspan="5" class="px-6 py-8 text-center text-sm text-gray-500">No products added yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Recent Observations --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Recent Observations</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Store</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($competitor->observations as $obs)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $obs->observed_at?->format('M d, Y') ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $obs->store?->name ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-{{ $obs->observation_type->color() }}-100 text-{{ $obs->observation_type->color() }}-800">
                                {{ $obs->observation_type->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $obs->competitorProduct?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $obs->quantity ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $obs->price ? number_format($obs->price, 2) : '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($obs->notes, 40) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-8 text-center text-sm text-gray-500">No observations recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
