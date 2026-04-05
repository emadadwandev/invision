<x-layouts.app :title="'Create Sales Order'">
    <div class="mb-6">
        <a href="{{ route('sales.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">&larr; Back to Sales Orders</a>
        <h1 class="text-2xl font-semibold text-gray-900 mt-2">Create Sales Order</h1>
    </div>

    <form method="POST" action="{{ route('sales.store') }}" x-data="salesOrderForm()" class="space-y-6">
        @csrf

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Order Details</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Store *</label>
                    <select name="store_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select Store</option>
                        @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ old('store_id') == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                        @endforeach
                    </select>
                    @error('store_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Notes</label>
                    <textarea name="notes" rows="2" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-medium text-gray-900">Order Items</h2>
                <button type="button" @click="addItem()" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md text-sm font-medium text-white bg-green-600 hover:bg-green-700">+ Add Item</button>
            </div>

            <template x-for="(item, index) in items" :key="index">
                <div class="grid grid-cols-12 gap-3 mb-4 items-end border-b pb-4">
                    <div class="col-span-4">
                        <label class="block text-xs font-medium text-gray-500">Product *</label>
                        <select :name="'items['+index+'][product_id]'" x-model="item.product_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Select Product</option>
                            @foreach($products as $product)
                            <option value="{{ $product->id }}" data-price="{{ $product->price }}">{{ $product->name }} ({{ $product->sku }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-500">Qty *</label>
                        <input type="number" :name="'items['+index+'][quantity]'" x-model.number="item.quantity" min="1" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-500">Unit Price *</label>
                        <input type="number" step="0.01" :name="'items['+index+'][unit_price]'" x-model.number="item.unit_price" min="0" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-xs font-medium text-gray-500">Discount %</label>
                        <input type="number" step="0.01" :name="'items['+index+'][discount_percent]'" x-model.number="item.discount_percent" min="0" max="100" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    </div>
                    <div class="col-span-1 text-right">
                        <label class="block text-xs font-medium text-gray-500">Line Total</label>
                        <p class="mt-1 text-sm font-medium text-gray-900" x-text="'$' + lineTotal(item).toFixed(2)"></p>
                    </div>
                    <div class="col-span-1">
                        <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-red-600 hover:text-red-900 text-sm">&times;</button>
                    </div>
                    <input type="hidden" :name="'items['+index+'][barcode_scanned]'" x-model="item.barcode_scanned">
                </div>
            </template>

            <div class="text-right mt-4 text-lg font-semibold text-gray-900">
                Order Total: $<span x-text="orderTotal().toFixed(2)">0.00</span>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('sales.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Cancel</a>
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Create Order</button>
        </div>
    </form>

    @push('scripts')
    <script>
        function salesOrderForm() {
            return {
                items: [{ product_id: '', quantity: 1, unit_price: 0, discount_percent: 0, barcode_scanned: '' }],
                addItem() {
                    this.items.push({ product_id: '', quantity: 1, unit_price: 0, discount_percent: 0, barcode_scanned: '' });
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                },
                lineTotal(item) {
                    const subtotal = item.quantity * item.unit_price;
                    const discount = subtotal * ((item.discount_percent || 0) / 100);
                    return subtotal - discount;
                },
                orderTotal() {
                    return this.items.reduce((sum, item) => sum + this.lineTotal(item), 0);
                }
            }
        }
    </script>
    @endpush
</x-layouts.app>
