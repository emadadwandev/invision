<x-layouts.app title="{{ $report['title'] }}">
    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between mb-6">
            <div>
                <div class="flex items-center gap-2 mb-1">
                    <a href="{{ route('reports.index') }}" class="text-indigo-600 hover:text-indigo-800 text-sm">&larr; All Reports</a>
                </div>
                <h1 class="text-2xl font-semibold text-gray-900">{{ $report['title'] }}</h1>
                <p class="text-sm text-gray-500">{{ $report['period'] ?? '' }} &middot; Generated {{ $report['generated'] }}</p>
            </div>
            <div class="mt-3 sm:mt-0 flex gap-2">
                <a href="{{ route('reports.export-excel', ['type' => $type] + ($filters ?? [])) }}"
                   class="inline-flex items-center px-3 py-2 bg-green-600 text-white text-sm rounded-md hover:bg-green-700">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Excel
                </a>
                <a href="{{ route('reports.export-pdf', ['type' => $type] + ($filters ?? [])) }}"
                   class="inline-flex items-center px-3 py-2 bg-red-600 text-white text-sm rounded-md hover:bg-red-700">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                    PDF
                </a>
            </div>
        </div>

        {{-- Filters --}}
        <form method="GET" action="{{ route('reports.show', $type) }}" class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date From</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}"
                           class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date To</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}"
                           class="w-full border-gray-300 rounded-md shadow-sm text-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                @if($type === 'stock-movement')
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Store</label>
                    <select name="store_id" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">All Stores</option>
                        @foreach(\App\Models\Store::where('is_active', true)->orderBy('name')->get() as $store)
                            <option value="{{ $store->id }}" {{ ($filters['store_id'] ?? '') == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Movement Type</label>
                    <select name="type" class="w-full border-gray-300 rounded-md shadow-sm text-sm">
                        <option value="">All Types</option>
                        @foreach(\App\Enums\StockMovementType::cases() as $smType)
                            <option value="{{ $smType->value }}" {{ ($filters['type'] ?? '') === $smType->value ? 'selected' : '' }}>{{ $smType->label() }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div class="flex items-end">
                    <button type="submit" class="w-full inline-flex justify-center items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
                        Apply Filters
                    </button>
                </div>
            </div>
        </form>

        {{-- Report Table --}}
        <div class="bg-white rounded-lg shadow overflow-hidden">
            @if(!empty($report['rows']))
                <div class="px-4 py-3 border-b border-gray-200 text-sm text-gray-500">
                    {{ count($report['rows']) }} rows
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                @foreach(array_keys($report['rows'][0]) as $header)
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        {{ ucwords(str_replace('_', ' ', $header)) }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($report['rows'] as $idx => $row)
                                <tr class="{{ $idx % 2 === 0 ? 'bg-white' : 'bg-gray-50' }}">
                                    <td class="px-4 py-2 text-sm text-gray-400">{{ $idx + 1 }}</td>
                                    @foreach($row as $value)
                                        <td class="px-4 py-2 text-sm text-gray-700 whitespace-nowrap">
                                            @if(is_numeric($value) && $value > 999)
                                                {{ number_format($value, is_float($value + 0) ? 2 : 0) }}
                                            @else
                                                {{ is_array($value) ? json_encode($value) : $value }}
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
                    <svg class="mx-auto w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <p class="text-lg font-medium">No data found</p>
                    <p class="text-sm mt-1">Try adjusting the date range or filters.</p>
                </div>
            @endif
        </div>
    </div>
</x-layouts.app>
