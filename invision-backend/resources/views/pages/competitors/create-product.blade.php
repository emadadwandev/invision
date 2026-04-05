<x-layouts.app :title="'Add Competitor Product'">
    <div class="mb-6">
        <a href="{{ route('competitors.products') }}" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Back to Products</a>
        <h1 class="text-2xl font-semibold text-gray-900 mt-1">Add Competitor Product</h1>
    </div>

    <form method="POST" action="{{ route('competitors.products.store') }}" class="bg-white shadow rounded-lg p-6 max-w-2xl">
        @csrf

        <div class="space-y-4">
            <div>
                <label for="competitor_id" class="block text-sm font-medium text-gray-700">Competitor *</label>
                <select name="competitor_id" id="competitor_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Select Competitor</option>
                    @foreach ($competitors as $comp)
                        <option value="{{ $comp->id }}" {{ old('competitor_id') == $comp->id ? 'selected' : '' }}>{{ $comp->name }}</option>
                    @endforeach
                </select>
                @error('competitor_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Product Name *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label for="sku" class="block text-sm font-medium text-gray-700">SKU</label>
                    <input type="text" name="sku" id="sku" value="{{ old('sku') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="barcode" class="block text-sm font-medium text-gray-700">Barcode</label>
                    <input type="text" name="barcode" id="barcode" value="{{ old('barcode') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
            </div>

            <div>
                <label for="category" class="block text-sm font-medium text-gray-700">Category</label>
                <input type="text" name="category" id="category" value="{{ old('category') }}"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description') }}</textarea>
            </div>

            <div class="flex items-center">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                       class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <label for="is_active" class="ml-2 block text-sm text-gray-900">Active</label>
            </div>
        </div>

        <div class="mt-6 flex gap-3">
            <button type="submit" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                Create Product
            </button>
            <a href="{{ route('competitors.products') }}" class="rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Cancel
            </a>
        </div>
    </form>
</x-layouts.app>
