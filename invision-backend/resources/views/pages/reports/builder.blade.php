<x-layouts.app title="Report Builder">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8" x-data="reportBuilder()">
        <div class="flex items-center gap-2 mb-6">
            <a href="{{ route('reports.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">&larr; Fixed Reports</a>
            <h1 class="text-2xl font-semibold text-gray-900 ml-2">Dynamic Report Builder</h1>
        </div>

        {{-- Builder Form --}}
        <form method="POST" action="{{ route('reports.builder.run') }}" id="builderForm">
            @csrf
            <div class="bg-white rounded-lg shadow p-6 mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    {{-- Entity --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Data Source</label>
                        <select name="entity" x-model="entity" @change="onEntityChange()"
                                class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            @foreach($entities as $key => $meta)
                                <option value="{{ $key }}" {{ (old('entity', $report['entity'] ?? '') === $key) ? 'selected' : '' }}>
                                    {{ $meta['label'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Group By --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Group By (optional)</label>
                        <select name="group_by" x-model="groupBy"
                                class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">No Grouping</option>
                            <template x-for="opt in currentGroupOptions" :key="opt">
                                <option :value="opt" x-text="opt.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Order By --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Order By</label>
                        <select name="order_by"
                                class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="created_at">Date Created</option>
                            <template x-for="col in currentColumns" :key="col">
                                <option :value="col" x-text="col.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase())"></option>
                            </template>
                        </select>
                    </div>

                    {{-- Order Direction --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Direction</label>
                        <select name="order_dir"
                                class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="desc">Descending</option>
                            <option value="asc">Ascending</option>
                        </select>
                    </div>

                    {{-- Limit --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Limit</label>
                        <input type="number" name="limit" value="{{ old('limit', 100) }}" min="1" max="5000"
                               class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>
                </div>

                {{-- Filters --}}
                <div class="mt-6 pt-4 border-t border-gray-200">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Filters</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Date From</label>
                            <input type="date" name="filters[date_from]" value="{{ old('filters.date_from') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Date To</label>
                            <input type="date" name="filters[date_to]" value="{{ old('filters.date_to') }}"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Status</label>
                            <input type="text" name="filters[status]" value="{{ old('filters.status') }}" placeholder="e.g. delivered"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        </div>
                        <div>
                            <label class="block text-xs text-gray-500 mb-1">Store ID</label>
                            <input type="number" name="filters[store_id]" value="{{ old('filters.store_id') }}" placeholder="Optional"
                                   class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="mt-6 flex gap-3">
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Run Report
                    </button>
                    @if(!empty($report))
                    <button type="submit" formaction="{{ route('reports.builder.export-excel') }}" class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700">
                        Excel Export
                    </button>
                    <button type="submit" formaction="{{ route('reports.builder.export-pdf') }}" class="inline-flex items-center px-3 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700">
                        PDF Export
                    </button>
                    @endif
                </div>
            </div>
        </form>

        {{-- Results --}}
        @if(!empty($report))
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-medium text-gray-900">{{ $report['title'] }}</h2>
                    <p class="text-sm text-gray-500">{{ $report['count'] ?? count($report['rows'] ?? []) }} rows &middot; {{ $report['generated'] }}</p>
                </div>
            </div>

            @if(!empty($report['rows']))
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                                @foreach(array_keys($report['rows'][0]) as $header)
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                        {{ ucwords(str_replace('_', ' ', $header)) }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($report['rows'] as $idx => $row)
                                <tr class="{{ $idx % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                                    <td class="px-4 py-2 text-sm text-gray-400">{{ $idx + 1 }}</td>
                                    @foreach($row as $key => $value)
                                        <td class="px-4 py-2 text-sm text-gray-700 whitespace-nowrap">
                                            @if(is_array($value))
                                                {{ json_encode($value) }}
                                            @elseif(is_numeric($value) && $value > 999)
                                                {{ number_format($value, is_float($value + 0) ? 2 : 0) }}
                                            @else
                                                {{ $value }}
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="p-12 text-center text-gray-400">
                    <p>No data found for the selected criteria.</p>
                </div>
            @endif
        </div>
        @endif
    </div>

    @push('scripts')
    <script>
        function reportBuilder() {
            const entities = @json($entities);
            return {
                entity: '{{ old('entity', $report['entity'] ?? 'sales_orders') }}',
                groupBy: '{{ old('group_by', '') }}',
                get currentColumns() { return entities[this.entity]?.columns || []; },
                get currentGroupOptions() { return entities[this.entity]?.group_by_options || []; },
                onEntityChange() { this.groupBy = ''; },
            };
        }
    </script>
    @endpush
</x-layouts.app>
