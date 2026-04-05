<x-layouts.app title="{{ $campaign->name }}">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('campaigns.index') }}" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">{{ $campaign->name }}</h1>
                @php $color = $campaign->status->color(); @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $color }}-100 text-{{ $color }}-800">
                    {{ $campaign->status->label() }}
                </span>
            </div>
            <p class="mt-1 text-sm text-gray-600">{{ $campaign->type->label() }}</p>
        </div>
        <div class="flex gap-2 mt-4 sm:mt-0">
            @can('update', $campaign)
            <a href="{{ route('campaigns.edit', $campaign) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Edit</a>
            @endcan
            @can('delete', $campaign)
            <form method="POST" action="{{ route('campaigns.destroy', $campaign) }}" onsubmit="return confirm('Delete this campaign?')">
                @csrf @method('DELETE')
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50">Delete</button>
            </form>
            @endcan
        </div>
    </div>

    {{-- Campaign Info --}}
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Campaign Information</h2>
        <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">Type</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $campaign->type->label() }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Start Date</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $campaign->start_date->format('M d, Y') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">End Date</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $campaign->end_date->format('M d, Y') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Budget</dt>
                <dd class="mt-1 text-sm text-gray-900">${{ number_format($campaign->budget ?? 0, 2) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Spent</dt>
                <dd class="mt-1 text-sm text-gray-900">${{ number_format($campaign->spent ?? 0, 2) }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Budget Utilization</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $campaign->budgetUtilization() }}%</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Created By</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $campaign->creator?->full_name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Created</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $campaign->created_at->format('M d, Y H:i') }}</dd>
            </div>
        </dl>
        @if($campaign->description)
        <div class="mt-4 pt-4 border-t border-gray-200">
            <dt class="text-sm font-medium text-gray-500">Description</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $campaign->description }}</dd>
        </div>
        @endif
    </div>

    {{-- Targeted Stores --}}
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Targeted Stores ({{ $campaign->stores->count() }})</h2>
        @if($campaign->stores->isEmpty())
            <p class="text-sm text-gray-500">No stores targeted.</p>
        @else
            <div class="flex flex-wrap gap-2">
                @foreach($campaign->stores as $store)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">{{ $store->name }}</span>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Targeted Products --}}
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Targeted Products ({{ $campaign->products->count() }})</h2>
        @if($campaign->products->isEmpty())
            <p class="text-sm text-gray-500">No products targeted.</p>
        @else
            <div class="flex flex-wrap gap-2">
                @foreach($campaign->products as $product)
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">{{ $product->name }}</span>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Recent Tasks --}}
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-medium text-gray-900">Recent Tasks ({{ $campaign->tasks->count() }})</h2>
            <a href="{{ route('campaigns.tasks', ['campaign_id' => $campaign->id]) }}" class="text-sm text-indigo-600 hover:text-indigo-900">View All</a>
        </div>
        @if($campaign->tasks->isEmpty())
            <p class="text-sm text-gray-500">No tasks assigned yet.</p>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Store</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Assigned To</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($campaign->tasks as $task)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $task->store->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $task->assignedUser->full_name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            @php $tc = $task->status->color(); @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $tc }}-100 text-{{ $tc }}-800">
                                {{ $task->status->label() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('campaigns.task-show', $task) }}" class="text-sm text-indigo-600 hover:text-indigo-900">View</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Recent Entries --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Recent Entries ({{ $campaign->entries->count() }})</h2>
        @if($campaign->entries->isEmpty())
            <p class="text-sm text-gray-500">No entries recorded yet.</p>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Store</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($campaign->entries as $entry)
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $entry->entry_type)) }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500 font-mono">{{ $entry->code ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $entry->store->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $entry->user->full_name ?? '—' }}</td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $entry->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>
</x-layouts.app>
