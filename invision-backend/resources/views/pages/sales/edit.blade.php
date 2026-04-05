<x-layouts.app :title="'Edit Order ' . $salesOrder->order_number">
    <div class="mb-6">
        <a href="{{ route('sales.show', $salesOrder) }}" class="text-indigo-600 hover:text-indigo-900 text-sm">&larr; Back to Order</a>
        <h1 class="text-2xl font-semibold text-gray-900 mt-2">Edit Order {{ $salesOrder->order_number }}</h1>
    </div>

    <form method="POST" action="{{ route('sales.update', $salesOrder) }}" class="space-y-6">
        @csrf @method('PUT')

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Order Details</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Store</label>
                    <select name="store_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ $salesOrder->store_id == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                        @endforeach
                    </select>
                    @error('store_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('notes', $salesOrder->notes) }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Current Items</h2>
            @if($salesOrder->items->count())
            <div class="space-y-2">
                @foreach($salesOrder->items as $item)
                <div class="flex items-center justify-between bg-gray-50 rounded p-3">
                    <div>
                        <span class="text-sm font-medium text-gray-900">{{ $item->product->name }}</span>
                        <span class="text-sm text-gray-500 ml-2">x{{ $item->quantity }} @ ${{ number_format($item->unit_price, 2) }}</span>
                    </div>
                    <span class="text-sm font-medium text-gray-900">${{ number_format($item->line_total, 2) }}</span>
                </div>
                @endforeach
            </div>
            <p class="text-xs text-gray-500 mt-3">To modify items, delete this order and create a new one, or use the API.</p>
            @endif
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('sales.show', $salesOrder) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Cancel</a>
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Update Order</button>
        </div>
    </form>
</x-layouts.app>
