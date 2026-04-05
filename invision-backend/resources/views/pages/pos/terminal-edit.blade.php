<x-layouts.app title="Edit POS Terminal">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit POS Terminal</h1>
    </div>

    <div class="bg-white shadow-sm sm:rounded-lg p-6">
        <form method="POST" action="{{ route('pos.terminal-update', $posTerminal) }}" class="space-y-6">
            @csrf @method('PUT')

            <div>
                <label for="store_id" class="block text-sm font-medium text-gray-700">Store</label>
                <select name="store_id" id="store_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">Select Store</option>
                    @foreach($stores as $store)
                        <option value="{{ $store->id }}" {{ old('store_id', $posTerminal->store_id) == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                    @endforeach
                </select>
                @error('store_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="terminal_code" class="block text-sm font-medium text-gray-700">Terminal Code</label>
                <input type="text" name="terminal_code" id="terminal_code" value="{{ old('terminal_code', $posTerminal->terminal_code) }}" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('terminal_code') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name', $posTerminal->name) }}" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="flex items-center">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $posTerminal->is_active) ? 'checked' : '' }}
                    class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <label for="is_active" class="ml-2 block text-sm text-gray-700">Active</label>
            </div>

            <div class="flex justify-end">
                <a href="{{ route('pos.terminals') }}" class="mr-3 inline-flex items-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">Update Terminal</button>
            </div>
        </form>
    </div>
</x-layouts.app>
