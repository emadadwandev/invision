<x-layouts.app title="Campaign Tasks">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Campaign Tasks</h1>
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('campaigns.tasks') }}" class="bg-white shadow rounded-lg p-4 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
            <div>
                <label for="campaign_id" class="block text-sm font-medium text-gray-700">Campaign</label>
                <select name="campaign_id" id="campaign_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">All Campaigns</option>
                    @foreach($campaigns as $campaign)
                        <option value="{{ $campaign->id }}" {{ request('campaign_id') == $campaign->id ? 'selected' : '' }}>{{ $campaign->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select name="status" id="status" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                    <option value="">All Statuses</option>
                    @foreach(App\Enums\TaskStatus::cases() as $s)
                        <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">Filter</button>
            </div>
            <div class="flex items-end">
                <a href="{{ route('campaigns.tasks') }}" class="w-full text-center px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded-md hover:bg-gray-50">Reset</a>
            </div>
        </div>
    </form>

    {{-- Tasks Table --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Campaign</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Store</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned To</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Completed</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($tasks as $task)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $task->campaign->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $task->store->name ?? '—' }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $task->assignedUser->full_name ?? '—' }}</td>
                    <td class="px-6 py-4">
                        @php $c = $task->status->color(); @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-{{ $c }}-100 text-{{ $c }}-800">
                            {{ $task->status->label() }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $task->completed_at?->format('M d, Y H:i') ?? '—' }}</td>
                    <td class="px-6 py-4 text-right">
                        <a href="{{ route('campaigns.task-show', $task) }}" class="text-sm text-indigo-600 hover:text-indigo-900">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-8 text-center text-sm text-gray-500">No tasks found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $tasks->withQueryString()->links() }}
    </div>
</x-layouts.app>
