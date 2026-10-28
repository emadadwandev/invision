{{-- Shared by create/edit. Expects: $stores, $frequency, $workDays, $initialStores --}}
@php
    $days = [7 => 'Sun', 1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat'];
    $cycles = collect(App\Enums\VisitFrequency::cases())->mapWithKeys(fn ($f) => [$f->value => $f->cycleWeeks()]);
@endphp

<div class="bg-white shadow rounded-lg p-6" x-data="routeSchedule(@js($initialStores), @js($frequency), @js($cycles))">
    <h2 class="text-lg font-medium text-gray-900 mb-4">Schedule</h2>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
            <label for="frequency" class="block text-sm font-medium text-gray-700">Frequency <span class="text-red-500">*</span></label>
            <select name="frequency" id="frequency" x-model="frequency" required
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                @foreach(App\Enums\VisitFrequency::cases() as $freq)
                    <option value="{{ $freq->value }}">{{ $freq->label() }}</option>
                @endforeach
            </select>
            <p class="mt-1 text-xs text-gray-500" x-show="frequency === 'daily'">Same stores every work day.</p>
            <p class="mt-1 text-xs text-gray-500" x-show="frequency === 'weekly'">Pick a day for each store, and the week repeats every week.</p>
            <p class="mt-1 text-xs text-gray-500" x-show="cycle > 1">Dynamic route: <span x-text="cycle"></span> different weeks rotate in a cycle. Set the week and day for each store.</p>
            @error('frequency') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
        </div>
        <div>
            <span class="block text-sm font-medium text-gray-700">Work Days</span>
            <div class="mt-2 flex flex-wrap gap-3">
                @foreach($days as $num => $label)
                    <label class="inline-flex items-center gap-1 text-sm text-gray-700">
                        <input type="checkbox" name="work_days[]" value="{{ $num }}" {{ in_array($num, $workDays) ? 'checked' : '' }}
                               class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        {{ $label }}
                    </label>
                @endforeach
            </div>
            <p class="mt-1 text-xs text-gray-500">Stores set to "Every work day" are visited on these days.</p>
        </div>
    </div>

    <h3 class="text-sm font-semibold text-gray-700 mb-1">Store Visits</h3>
    <p class="text-sm text-gray-500 mb-4">
        Add stores in visit order. Use <strong>Specific week</strong> when one week is different: the stores you add for that week replace the regular schedule for that week only.
    </p>

    <template x-for="(item, index) in stores" :key="index">
        <div class="flex flex-wrap items-center gap-3 mb-3 p-3 rounded-lg" :class="item.week_start ? 'bg-amber-50' : 'bg-gray-50'">
            <span class="text-sm font-bold text-gray-400 w-6" x-text="index + 1"></span>
            <select :name="`stores[${index}][store_id]`" x-model="item.store_id" required
                    class="flex-1 min-w-48 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Select store...</option>
                @foreach($stores as $store)
                    <option value="{{ $store->id }}">{{ $store->name }} ({{ $store->code }})</option>
                @endforeach
            </select>
            <select :name="`stores[${index}][day_of_week]`" x-model="item.day_of_week" x-show="frequency !== 'daily'"
                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Every work day</option>
                @foreach($days as $num => $label)
                    <option value="{{ $num }}">{{ $label }}</option>
                @endforeach
            </select>
            <select :name="`stores[${index}][week_number]`" x-model="item.week_number" x-show="cycle > 1 && !item.week_start"
                    class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                <option value="">Every week</option>
                <template x-for="w in cycle" :key="w">
                    <option :value="w" x-text="`Week ${w}`" :selected="String(item.week_number) === String(w)"></option>
                </template>
            </select>
            <label class="flex items-center gap-1 text-xs text-gray-500">
                Specific week
                <input type="date" :name="`stores[${index}][week_start]`" x-model="item.week_start" title="Any date in the week to override"
                       class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </label>
            <input type="hidden" :name="`stores[${index}][visit_order]`" :value="index + 1">
            <input type="number" :name="`stores[${index}][expected_duration_minutes]`" x-model="item.duration"
                   placeholder="Min" min="1" max="480"
                   class="w-20 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <button type="button" @click="stores.splice(index, 1)" class="text-red-500 hover:text-red-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
            </button>
        </div>
    </template>

    <button type="button" @click="stores.push({ store_id: '', day_of_week: '', week_number: '', week_start: '', duration: '' })"
            class="mt-2 text-sm text-indigo-600 hover:text-indigo-800 font-medium">
        + Add Store to Route
    </button>
</div>

@push('scripts')
<script>
function routeSchedule(stores, frequency, cycles) {
    return {
        // old() input uses the request field names; normalise so every row has all keys
        stores: stores.map(s => ({ store_id: '', day_of_week: '', week_number: '', week_start: '', ...s, duration: s.duration ?? s.expected_duration_minutes ?? '' })),
        frequency,
        get cycle() { return cycles[this.frequency] ?? 1; },
    };
}
</script>
@endpush
