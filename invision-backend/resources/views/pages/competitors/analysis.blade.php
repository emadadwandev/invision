<x-layouts.app :title="'Competitor Analysis'">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('competitors.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Back to Competitors</a>
            <h1 class="text-2xl font-semibold text-gray-900 mt-1">Competitor Analysis</h1>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mb-6 flex gap-4">
        <select name="store_id" onchange="this.form.submit()" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="">All Stores</option>
            @foreach ($stores as $store)
                <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}"
               class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        <input type="date" name="to" value="{{ request('to') }}"
               class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            Apply
        </button>
    </form>

    @if (empty($data))
        <div class="bg-white shadow rounded-lg p-12 text-center text-sm text-gray-500">
            No competitor data available for the selected filters.
        </div>
    @else
        <div class="space-y-6">
            @foreach ($data as $item)
                <div class="bg-white shadow rounded-lg overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <h3 class="text-lg font-medium text-gray-900">{{ $item['competitor'] }}</h3>
                        <span class="text-sm text-gray-500">{{ $item['total_observations'] }} total observations</span>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            @foreach ($item['types'] as $typeData)
                                <div class="border rounded-lg p-4">
                                    <p class="text-xs text-gray-500 uppercase">{{ $typeData['type'] }}</p>
                                    <p class="text-lg font-semibold text-gray-900">{{ $typeData['count'] }} obs</p>
                                    @if ($typeData['avg_price'])
                                        <p class="text-sm text-gray-500">Avg Price: {{ number_format($typeData['avg_price'], 2) }}</p>
                                    @endif
                                    @if ($typeData['total_quantity'])
                                        <p class="text-sm text-gray-500">Total Qty: {{ $typeData['total_quantity'] }}</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-layouts.app>
