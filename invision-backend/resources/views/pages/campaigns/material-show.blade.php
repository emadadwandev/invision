<x-layouts.app title="{{ $material->name }}">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('campaigns.materials') }}" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">{{ $material->name }}</h1>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $material->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                    {{ $material->is_active ? 'Active' : 'Inactive' }}
                </span>
            </div>
            <p class="mt-1 text-sm text-gray-600">{{ $material->type ?? 'POSM Material' }}</p>
        </div>
        <a href="{{ route('campaigns.materials') }}" class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Back</a>
    </div>

    {{-- Material Info --}}
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Material Information</h2>
        <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">Name</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $material->name }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Type</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $material->type ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">SKU</dt>
                <dd class="mt-1 text-sm text-gray-900 font-mono">{{ $material->sku ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Quantity Available</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $material->quantity_available ?? 0 }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Description</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $material->description ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Created</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $material->created_at->format('M d, Y H:i') }}</dd>
            </div>
        </dl>
    </div>

    {{-- Placements --}}
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Placements ({{ $material->placements->count() }})</h2>
        @if($material->placements->isEmpty())
            <p class="text-sm text-gray-500">No placements recorded.</p>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Store</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Condition</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Placed At</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Last Checked</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Checks</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($material->placements as $placement)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $placement->store->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @php $pc = $placement->condition->color(); @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $pc }}-100 text-{{ $pc }}-800">
                                {{ $placement->condition->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $placement->placed_at?->format('M d, Y') ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $placement->last_checked_at?->format('M d, Y H:i') ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $placement->checkLogs->count() }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Recent Check Logs --}}
    @php $allLogs = $material->placements->flatMap->checkLogs->sortByDesc('created_at')->take(20); @endphp
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Recent Check Logs</h2>
        @if($allLogs->isEmpty())
            <p class="text-sm text-gray-500">No check logs recorded.</p>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Condition</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Replacement</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($allLogs as $log)
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ $log->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-4 py-2">
                            @php $lc = $log->condition->color(); @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $lc }}-100 text-{{ $lc }}-800">
                                {{ $log->condition->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-2 text-sm">
                            @if($log->replacement_requested)
                            <span class="text-red-600 font-medium">Requested</span>
                            @else
                            <span class="text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-500">{{ $log->notes ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layouts.app>
