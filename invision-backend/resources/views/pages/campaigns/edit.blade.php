<x-layouts.app title="Edit Campaign">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Edit Campaign</h1>
        <p class="mt-1 text-sm text-gray-600">Update campaign information.</p>
    </div>

    <form method="POST" action="{{ route('campaigns.update', $campaign) }}" class="space-y-6">
        @csrf @method('PUT')

        <div class="bg-white shadow rounded-lg p-6 space-y-4">
            <h2 class="text-lg font-medium text-gray-900">Campaign Details</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $campaign->name) }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700">Type *</label>
                    <select name="type" id="type" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                        @foreach(App\Enums\CampaignType::cases() as $t)
                            <option value="{{ $t->value }}" {{ old('type', $campaign->type->value) === $t->value ? 'selected' : '' }}>{{ $t->label() }}</option>
                        @endforeach
                    </select>
                    @error('type') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                <textarea name="description" id="description" rows="3"
                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">{{ old('description', $campaign->description) }}</textarea>
                @error('description') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date *</label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $campaign->start_date->format('Y-m-d')) }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('start_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date *</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $campaign->end_date->format('Y-m-d')) }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('end_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="budget" class="block text-sm font-medium text-gray-700">Budget ($)</label>
                    <input type="number" step="0.01" name="budget" id="budget" value="{{ old('budget', $campaign->budget) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @error('budget') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    @foreach(App\Enums\CampaignStatus::cases() as $s)
                        <option value="{{ $s->value }}" {{ old('status', $campaign->status->value) === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Targeting --}}
        <div class="bg-white shadow rounded-lg p-6 space-y-4">
            <h2 class="text-lg font-medium text-gray-900">Targeting</h2>

            @php $selectedStores = old('store_ids', $campaign->stores->pluck('id')->toArray()); @endphp
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Target Stores</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 max-h-48 overflow-y-auto border rounded-md p-3">
                    @foreach($stores as $store)
                    <label class="flex items-center text-sm">
                        <input type="checkbox" name="store_ids[]" value="{{ $store->id }}"
                               {{ in_array($store->id, $selectedStores) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 mr-2">
                        {{ $store->name }}
                    </label>
                    @endforeach
                </div>
            </div>

            @php $selectedProducts = old('product_ids', $campaign->products->pluck('id')->toArray()); @endphp
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Target Products</label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 max-h-48 overflow-y-auto border rounded-md p-3">
                    @foreach($products as $product)
                    <label class="flex items-center text-sm">
                        <input type="checkbox" name="product_ids[]" value="{{ $product->id }}"
                               {{ in_array($product->id, $selectedProducts) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 mr-2">
                        {{ $product->name }}
                    </label>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-3">
            <a href="{{ route('campaigns.show', $campaign) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-md hover:bg-indigo-700">Update Campaign</button>
        </div>
    </form>
</x-layouts.app>
