<x-layouts.app title="Edit Category">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Category: {{ $category->name }}</h1>
    </div>

    <form method="POST" action="{{ route('product-categories.update', $category) }}" class="bg-white shadow rounded-lg p-6 space-y-6">
        @csrf @method('PUT')
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <label class="block text-sm font-medium text-gray-700">Name *</label>
                <input type="text" name="name" value="{{ old('name', $category->name) }}" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Parent Category</label>
                <select name="parent_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">— Root (No Parent) —</option>
                    @foreach($parentCategories as $parent)
                        <option value="{{ $parent->id }}" {{ old('parent_id', $category->parent_id) == $parent->id ? 'selected' : '' }}>{{ $parent->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order) }}" min="0"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div class="flex items-center gap-2 mt-6">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $category->is_active) ? 'checked' : '' }}
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <label class="text-sm font-medium text-gray-700">Active</label>
            </div>
        </div>
        <div class="flex justify-end gap-3">
            <a href="{{ route('product-categories.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm hover:bg-indigo-700">Update Category</button>
        </div>
    </form>
</x-layouts.app>
