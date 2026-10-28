<x-layouts.app title="Edit Route Plan">
    <div class="mb-6">
        <div class="flex items-center gap-2">
            <a href="{{ route('routes.show', $route) }}" class="text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Edit: {{ $route->name }}</h1>
        </div>
    </div>

    <form method="POST" action="{{ route('routes.update', $route) }}" class="space-y-6">
        @csrf @method('PUT')

        <div class="bg-white shadow rounded-lg p-6">
            <h2 class="text-lg font-medium text-gray-900 mb-4">Route Details</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Name <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="name" value="{{ old('name', $route->name) }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="assigned_to" class="block text-sm font-medium text-gray-700">Assign To <span class="text-red-500">*</span></label>
                    <select name="assigned_to" id="assigned_to" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">Select user...</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" {{ old('assigned_to', $route->assigned_to) == $user->id ? 'selected' : '' }}>
                                {{ $user->full_name }} ({{ $user->role->label() }})
                            </option>
                        @endforeach
                    </select>
                    @error('assigned_to') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        @foreach(App\Enums\RouteStatus::cases() as $status)
                            <option value="{{ $status->value }}" {{ old('status', $route->status->value) === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date <span class="text-red-500">*</span></label>
                    <input type="date" name="start_date" id="start_date" value="{{ old('start_date', $route->start_date->toDateString()) }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div>
                    <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                    <input type="date" name="end_date" id="end_date" value="{{ old('end_date', $route->end_date?->toDateString()) }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                </div>
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description', $route->description) }}</textarea>
                </div>
            </div>
        </div>

        @include('pages.routes._schedule', [
            'frequency' => old('frequency', $route->frequency->value),
            'workDays' => array_map('intval', old('work_days', $route->work_days ?? [7, 1, 2, 3, 4])),
            'initialStores' => old('stores') ? collect(old('stores'))->values() : $route->routeStores->map(fn ($rs) => ['store_id' => (string) $rs->store_id, 'day_of_week' => (string) ($rs->day_of_week ?? ''), 'week_number' => (string) ($rs->week_number ?? ''), 'week_start' => $rs->week_start?->toDateString() ?? '', 'duration' => $rs->expected_duration_minutes ?? '']),
        ])

        <div class="flex justify-end gap-3">
            <a href="{{ route('routes.show', $route) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-indigo-600 border border-transparent rounded-md text-sm font-medium text-white hover:bg-indigo-700">Update Route Plan</button>
        </div>
    </form>

</x-layouts.app>
