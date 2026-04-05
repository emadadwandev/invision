<x-layouts.app title="Add District">
    <div class="mb-6">
        <a href="{{ route('geography.index', ['tab' => 'districts']) }}" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Back to Geography</a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Add District</h1>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <form method="POST" action="{{ route('geography.districts.store') }}" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">District Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('name') border-red-500 @enderror">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="city_id" class="block text-sm font-medium text-gray-700">City *</label>
                    <select name="city_id" id="city_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('city_id') border-red-500 @enderror">
                        <option value="">Select city...</option>
                        @foreach($cities as $city)
                            <option value="{{ $city->id }}" {{ old('city_id') == $city->id ? 'selected' : '' }}>{{ $city->name }}</option>
                        @endforeach
                    </select>
                    @error('city_id') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="flex justify-end space-x-3">
                <a href="{{ route('geography.index', ['tab' => 'districts']) }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Create District</button>
            </div>
        </form>
    </div>
</x-layouts.app>
