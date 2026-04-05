<x-layouts.app title="{{ $store->name }}">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $store->name }}</h1>
            <p class="mt-1 text-sm text-gray-600">{{ $store->code ? "Code: {$store->code}" : 'Store Details' }}</p>
        </div>
        <div class="mt-4 sm:mt-0 flex gap-3">
            @can('update', $store)
            <a href="{{ route('stores.edit', $store) }}" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Edit</a>
            <a href="{{ route('stores.products.edit', $store) }}" class="px-4 py-2 bg-emerald-600 text-white rounded-md text-sm hover:bg-emerald-700">Assign Products</a>
            @endcan
            <a href="{{ route('stores.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-50">Back</a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Store Information</h2>
            <dl class="space-y-3">
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Category</dt><dd class="text-sm font-medium text-gray-900">{{ $store->category->label() }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Rank</dt><dd class="text-sm font-medium text-gray-900">{{ $store->rank->label() }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Area</dt><dd class="text-sm font-medium text-gray-900">{{ $store->area?->name ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Address</dt><dd class="text-sm font-medium text-gray-900">{{ $store->address ?? '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-gray-500">GPS</dt><dd class="text-sm font-medium text-gray-900">{{ $store->gps_latitude && $store->gps_longitude ? "{$store->gps_latitude}, {$store->gps_longitude}" : '—' }}</dd></div>
                <div class="flex justify-between"><dt class="text-sm text-gray-500">Status</dt><dd><span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $store->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">{{ $store->is_active ? 'Active' : 'Inactive' }}</span></dd></div>
            </dl>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Contacts</h2>
            @if($store->contacts->isEmpty())
                <p class="text-sm text-gray-500">No contacts assigned.</p>
            @else
                <ul class="divide-y divide-gray-200">
                    @foreach($store->contacts as $contact)
                    <li class="py-3">
                        <p class="text-sm font-medium text-gray-900">{{ $contact->name }} @if($contact->is_primary)<span class="text-xs text-indigo-600">(Primary)</span>@endif</p>
                        <p class="text-xs text-gray-500">{{ $contact->position ?? '' }} {{ $contact->phone ? "· {$contact->phone}" : '' }}</p>
                    </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>

    <div class="mt-6 bg-white shadow rounded-lg p-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-900">Assigned Products</h2>
            @can('update', $store)
            <a href="{{ route('stores.products.edit', $store) }}" class="inline-flex items-center text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Manage
            </a>
            @endcan
        </div>
        @if($store->products->isEmpty())
            <p class="text-sm text-gray-500">No products assigned to this store.</p>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50"><tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">SKU</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                </tr></thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($store->products as $product)
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ $product->name }}</td>
                        <td class="px-4 py-2 text-sm text-gray-500">{{ $product->sku ?? '—' }}</td>
                        <td class="px-4 py-2 text-sm text-gray-500">{{ $product->category?->name ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layouts.app>
