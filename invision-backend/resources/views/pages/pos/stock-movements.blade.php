<x-layouts.app title="Stock Movements">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Stock Movements</h1>
    </div>

    <form method="GET" action="{{ route('pos.stock-movements') }}" class="mb-6 grid grid-cols-1 md:grid-cols-6 gap-4">
        <select name="store_id" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="">All Stores</option>
            @foreach($stores as $store)
                <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
            @endforeach
        </select>
        <select name="product_id" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="">All Products</option>
            @foreach($products as $product)
                <option value="{{ $product->id }}" {{ request('product_id') == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
            @endforeach
        </select>
        <select name="type" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="">All Types</option>
            @foreach(\App\Enums\StockMovementType::cases() as $type)
                <option value="{{ $type->value }}" {{ request('type') === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        <button type="submit" class="inline-flex items-center justify-center rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
            Filter
        </button>
    </form>

    <!-- Record Stock Movement Form -->
    <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6" x-data="{ showForm: false }">
        <button @click="showForm = !showForm" class="text-sm text-indigo-600 hover:text-indigo-500 font-medium">
            <span x-text="showForm ? '− Hide Form' : '+ Record Stock Movement'"></span>
        </button>
        <form x-show="showForm" method="POST" action="{{ route('pos.record-stock-movement') }}" class="mt-4 grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
            @csrf
            <div>
                <label class="block text-xs text-gray-500">Store</label>
                <select name="store_id" required class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value="">Select</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}">{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500">Product</label>
                <select name="product_id" required class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    <option value="">Select</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500">Type</label>
                <select name="type" required class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
                    @foreach(\App\Enums\StockMovementType::cases() as $type)
                        <option value="{{ $type->value }}">{{ $type->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500">Quantity</label>
                <input type="number" name="quantity" min="1" required class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500">Notes</label>
                <input type="text" name="notes" class="block w-full rounded-md border-gray-300 shadow-sm sm:text-sm">
            </div>
            <div>
                <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Record</button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Store</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Type</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Notes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($movements as $movement)
                <tr>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $movement->created_at?->format('M d, Y H:i') }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $movement->store?->name }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $movement->product?->name }}</td>
                    <td class="whitespace-nowrap px-6 py-4">
                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-{{ $movement->type->color() }}-100 text-{{ $movement->type->color() }}-800">
                            {{ $movement->type->label() }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-right font-medium {{ $movement->quantity > 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $movement->user?->first_name }} {{ $movement->user?->last_name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $movement->notes ?? '-' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">No stock movements found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $movements->links() }}</div>
</x-layouts.app>
