<x-layouts.app title="Sales Inquiry">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Sales Inquiry</h1>
        <p class="mt-1 text-sm text-gray-600">Browse and filter sales orders with payment status.</p>
    </div>

    {{-- Filters --}}
    <div class="bg-white shadow rounded-lg p-4 mb-6">
        <form method="GET" class="grid grid-cols-1 gap-4 sm:grid-cols-6">
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Order #..."
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Statuses</option>
                    @foreach (\App\Enums\OrderStatus::cases() as $s)
                        <option value="{{ $s->value }}" @selected(($filters['status'] ?? '') === $s->value)>{{ $s->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">Store</label>
                <select name="store_id" class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="">All Stores</option>
                    @foreach (\App\Models\Store::where('is_active', true)->orderBy('name')->get() as $store)
                        <option value="{{ $store->id }}" @selected(($filters['store_id'] ?? '') == $store->id)>{{ $store->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">From</label>
                <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">To</label>
                <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                       class="w-full rounded-md border-gray-300 shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">Filter</button>
                <a href="{{ route('inquiry.sales') }}" class="px-4 py-2 bg-gray-200 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-300">Reset</a>
            </div>
        </form>
    </div>

    {{-- Results Table --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-200">
            <span class="text-sm text-gray-600">{{ $orders->count() }} orders found</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Store</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Salesperson</th>
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Total</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Paid</th>
                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Balance</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($orders as $order)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm font-mono text-gray-900">{{ $order['order_number'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $order['created_at'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $order['store_name'] ?? '-' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-600">{{ $order['salesperson'] ?? '-' }}</td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $statusColors = ['draft' => 'gray', 'pending' => 'yellow', 'confirmed' => 'blue', 'delivered' => 'green', 'cancelled' => 'red'];
                                $color = $statusColors[$order['status']] ?? 'gray';
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">{{ ucfirst($order['status']) }}</span>
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600">${{ number_format($order['subtotal'], 2) }}</td>
                        <td class="px-4 py-3 text-sm text-right font-medium text-gray-900">${{ number_format($order['total'], 2) }}</td>
                        <td class="px-4 py-3 text-sm text-right text-green-600">${{ number_format($order['paid'], 2) }}</td>
                        <td class="px-4 py-3 text-sm text-right {{ $order['balance_due'] > 0 ? 'text-red-600 font-medium' : 'text-gray-600' }}">${{ number_format($order['balance_due'], 2) }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-sm text-gray-400">No orders found matching your criteria.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.app>
