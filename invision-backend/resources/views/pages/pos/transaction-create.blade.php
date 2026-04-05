<x-layouts.app title="New POS Transaction">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">New POS Transaction</h1>
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg p-6" x-data="posTransactionForm()">
        <form method="POST" action="{{ route('pos.transaction-store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="store_id" class="block text-sm font-medium text-gray-700">Store</label>
                    <select name="store_id" id="store_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select Store</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}">{{ $store->name }}</option>
                        @endforeach
                    </select>
                    @error('store_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Type</label>
                    <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @foreach(\App\Enums\PosTransactionType::cases() as $type)
                            <option value="{{ $type->value }}">{{ $type->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="pos_terminal_id" class="block text-sm font-medium text-gray-700">Terminal (Optional)</label>
                    <select name="pos_terminal_id" id="pos_terminal_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">No Terminal</option>
                        @foreach($terminals as $terminal)
                            <option value="{{ $terminal->id }}">{{ $terminal->name }} ({{ $terminal->terminal_code }})</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="payment_method" class="block text-sm font-medium text-gray-700">Payment Method</label>
                    <select name="payment_method" id="payment_method" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select</option>
                        <option value="cash">Cash</option>
                        <option value="card">Card</option>
                        <option value="bank_transfer">Bank Transfer</option>
                    </select>
                </div>
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-700">Notes</label>
                    <input type="text" name="notes" id="notes" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
            </div>

            <!-- Items -->
            <div>
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-lg font-medium text-gray-900">Items</h3>
                    <button type="button" @click="addItem()" class="text-sm text-indigo-600 hover:text-indigo-500">+ Add Item</button>
                </div>

                <template x-for="(item, index) in items" :key="index">
                    <div class="grid grid-cols-12 gap-2 mb-2 items-end">
                        <div class="col-span-4">
                            <label class="block text-xs text-gray-500" x-show="index === 0">Product</label>
                            <select :name="`items[${index}][product_id]`" required class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value="">Select Product</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}">{{ $product->name }} ({{ $product->sku }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs text-gray-500" x-show="index === 0">Quantity</label>
                            <input type="number" :name="`items[${index}][quantity]`" x-model.number="item.quantity" min="1" required
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" @input="calcLineTotal(index)">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs text-gray-500" x-show="index === 0">Unit Price</label>
                            <input type="number" :name="`items[${index}][unit_price]`" x-model.number="item.unit_price" step="0.01" min="0" required
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" @input="calcLineTotal(index)">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-xs text-gray-500" x-show="index === 0">Discount</label>
                            <input type="number" :name="`items[${index}][discount_amount]`" x-model.number="item.discount_amount" step="0.01" min="0"
                                class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" @input="calcLineTotal(index)">
                        </div>
                        <div class="col-span-1 text-right text-sm font-medium text-gray-900 pb-2" x-text="'$' + item.line_total.toFixed(2)"></div>
                        <div class="col-span-1 pb-2">
                            <button type="button" @click="removeItem(index)" x-show="items.length > 1" class="text-red-500 hover:text-red-700 text-sm">&times;</button>
                        </div>
                    </div>
                </template>

                <div class="mt-4 text-right">
                    <span class="text-lg font-bold text-gray-900">Total: $<span x-text="orderTotal.toFixed(2)">0.00</span></span>
                </div>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('pos.transactions') }}" class="mr-3 inline-flex items-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Create Transaction</button>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        function posTransactionForm() {
            return {
                items: [{ quantity: 1, unit_price: 0, discount_amount: 0, line_total: 0 }],
                get orderTotal() {
                    return this.items.reduce((sum, item) => sum + item.line_total, 0);
                },
                addItem() {
                    this.items.push({ quantity: 1, unit_price: 0, discount_amount: 0, line_total: 0 });
                },
                removeItem(index) {
                    this.items.splice(index, 1);
                },
                calcLineTotal(index) {
                    const item = this.items[index];
                    item.line_total = (item.quantity * item.unit_price) - (item.discount_amount || 0);
                    if (item.line_total < 0) item.line_total = 0;
                }
            };
        }
    </script>
    @endpush
</x-layouts.app>
