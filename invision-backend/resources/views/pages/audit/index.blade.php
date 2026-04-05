<x-layouts.app title="Audit Trail">
    <div class="container mx-auto py-6 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Audit Trail</h1>
        </div>

        <!-- Filters -->
        <form method="GET" action="{{ route('audit.index') }}" class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                    <select name="action" class="w-full rounded border-gray-300">
                        <option value="">All Actions</option>
                        @foreach (['create', 'update', 'delete', 'login', 'logout', 'mfa_enabled', 'mfa_disabled'] as $action)
                            <option value="{{ $action }}" @selected(request('action') === $action)>
                                {{ ucfirst(str_replace('_', ' ', $action)) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Entity Type</label>
                    <input type="text" name="entity_type" value="{{ request('entity_type') }}"
                           class="w-full rounded border-gray-300" placeholder="e.g. Store, Product">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
                    <input type="date" name="from" value="{{ request('from') }}"
                           class="w-full rounded border-gray-300">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                    <input type="date" name="to" value="{{ request('to') }}"
                           class="w-full rounded border-gray-300">
                </div>
                <div class="flex items-end">
                    <button type="submit"
                            class="w-full bg-indigo-600 text-white py-2 px-4 rounded hover:bg-indigo-700 transition">
                        Filter
                    </button>
                </div>
            </div>
        </form>

        <!-- Audit Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Entity</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP Address</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase"></th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $log->created_at->format('Y-m-d H:i:s') }}
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-900">
                                {{ $log->user_name ?? 'System' }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($log->action === 'create') bg-green-100 text-green-800
                                    @elseif($log->action === 'update') bg-blue-100 text-blue-800
                                    @elseif($log->action === 'delete') bg-red-100 text-red-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600">
                                {{ $log->entity_type }}
                                @if($log->entity_id)
                                    <span class="text-gray-400">#{{ $log->entity_id }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-500">{{ $log->ip_address }}</td>
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ route('audit.show', $log->id) }}"
                                   class="text-indigo-600 hover:text-indigo-900">View</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">No audit logs found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $logs->withQueryString()->links() }}
        </div>
    </div>
</x-layouts.app>
