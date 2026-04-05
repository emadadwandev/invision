<x-layouts.app title="Create Route Plan">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Create Route Plan</h1>
        <p class="mt-1 text-sm text-gray-600">Define a new route plan with store visit sequence.</p>
    </div>

    <form method="POST" action="{{ route('routes.store') }}" class="space-y-6">
        @csrf

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Route Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="assigned_to" class="block text-sm font-medium text-gray-700">Assign To <span class="text-red-500">*</span></label>
                    <select name="assigned_to" id="assigned_to" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select user...</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('assigned_to') == $user->id ? 'selected' : '' }}>
                                {{ $user->full_name }} ({{ $user->role->label() }})
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="frequency" class="block text-sm font-medium text-gray-700">Frequency <span class="text-red-500">*</span></label>
                    <select name="frequency" id="frequency" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @foreach(App\Enums\VisitFrequency::cases() as $freq)
                            <option value="{{ $freq->value }}" {{ old('frequency') === $freq->value ? 'selected' : '' }}>{{ $freq->label() }}</option>
                        @endforeach
                    </select>
                    @error('frequency') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @foreach(App\Enums\RouteStatus::cases() as $status)
                            <option value="{{ $status->value }}" {{ old('status', 'draft') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date <span class="text-red-500">*</span></label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date') }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('start_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('end_date') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Store Sequence --}}
        <div class="bg-white shadow rounded-lg p-6" x-data="routeStores()">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Store Sequence</h2>
            <p class="text-sm text-gray-500 mb-4">Add stores in visit order. The field rep will visit them in this sequence.</p>

            <template x-for="(item, index) in stores" :key="index">
                <div class="flex items-center gap-3 mb-3 p-3 bg-gray-50 rounded-lg">
                    <span class="text-sm font-bold text-gray-400 w-6" x-text="index + 1"></span>
                    <select :name="`stores[${index}][store_id]`" x-model="item.store_id" required
                            class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select store...</option>
                        @foreach($stores as $store)
                            <option value="{{ $store->id }}">{{ $store->name }} ({{ $store->code }})</option>
                        @endforeach
                    </select>
                    <input type="hidden" :name="`stores[${index}][visit_order]`" :value="index + 1">
                    <input type="number" :name="`stores[${index}][expected_duration_minutes]`" x-model="item.duration"
                           placeholder="Min" min="1" max="480"
                           class="w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <button type="button" @click="removeStore(index)" class="text-red-500 hover:text-red-700">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                    </button>
                </div>
            </template>

            <button type="button" @click="addStore()" class="mt-2 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
                + Add Store to Route
            </button>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('routes.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700">Create Route Plan</button>
        </div>
    </form>

    @push('scripts')
    <script>
    function routeStores() {
        return {
            stores: [],
            addStore() {
                this.stores.push({ store_id: '', duration: '' });
            },
            removeStore(index) {
                this.stores.splice(index, 1);
            }
        };
    }
    </script>
    @endpush
</x-layouts.app>
