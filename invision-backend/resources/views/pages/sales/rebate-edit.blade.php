<x-layouts.app :title="'Edit Rebate'">
    <div class="mb-6">
        <a href="{{ route('sales.rebates') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">&larr; Back to Rebates</a>
        <h1 class="text-2xl font-semibold text-gray-900 mt-2">Edit Rebate: {{ $rebate->name }}</h1>
    </div>

    <form method="POST" action="{{ route('sales.rebate-update', $rebate) }}" class="space-y-6">
        @csrf @method('PUT')

        <div class="bg-white shadow rounded-lg p-6">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name *</label>
                    <input type="text" name="name" value="{{ old('name', $rebate->name) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Type *</label>
                    <select name="type" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="percentage" {{ old('type', $rebate->type) === 'percentage' ? 'selected' : '' }}>Percentage</option>
                        <option value="fixed" {{ old('type', $rebate->type) === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                        <option value="tiered" {{ old('type', $rebate->type) === 'tiered' ? 'selected' : '' }}>Tiered</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Value *</label>
                    <input type="number" step="0.01" name="value" value="{{ old('value', $rebate->value) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Product (optional)</label>
                    <select name="product_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">All Products</option>
                        @foreach($products as $product)
                        <option value="{{ $product->id }}" {{ old('product_id', $rebate->product_id) == $product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Min Quantity</label>
                    <input type="number" name="min_quantity" value="{{ old('min_quantity', $rebate->min_quantity) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Max Quantity</label>
                    <input type="number" name="max_quantity" value="{{ old('max_quantity', $rebate->max_quantity) }}" min="0" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Start Date *</label>
                    <input type="date" name="start_date" value="{{ old('start_date', $rebate->start_date->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">End Date *</label>
                    <input type="date" name="end_date" value="{{ old('end_date', $rebate->end_date->format('Y-m-d')) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label class="flex items-center mt-6">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $rebate->is_active) ? 'checked' : '' }} class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <span class="ml-2 text-sm text-gray-700">Active</span>
                    </label>
                </div>
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $rebate->description) }}</textarea>
                </div>
            </div>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('sales.rebates') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Cancel</a>
            <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Update Rebate</button>
        </div>
    </form>
</x-layouts.app>
