<x-layouts.app title="Audit Log Detail">
    <div class="container mx-auto py-6 px-4">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Audit Log #{{ $log->id }}</h1>
            <a href="{{ route('audit.index') }}"
               class="bg-gray-200 text-gray-700 py-2 px-4 rounded hover:bg-gray-300 transition">
                &larr; Back to Audit Trail
            </a>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Overview -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Event Details</h2>
                <dl class="space-y-3">
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Date</dt>
                        <dd class="text-sm text-gray-900">{{ $log->created_at->format('Y-m-d H:i:s') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">User</dt>
                        <dd class="text-sm text-gray-900">{{ $log->user_name ?? 'System' }} (ID: {{ $log->user_id ?? '—' }})</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Action</dt>
                        <dd class="text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                @if($log->action === 'create') bg-green-100 text-green-800
                                @elseif($log->action === 'update') bg-blue-100 text-blue-800
                                @elseif($log->action === 'delete') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ ucfirst(str_replace('_', ' ', $log->action)) }}
                            </span>
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Entity</dt>
                        <dd class="text-sm text-gray-900">{{ $log->entity_type }} #{{ $log->entity_id ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">IP Address</dt>
                        <dd class="text-sm text-gray-900">{{ $log->ip_address ?? '—' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-sm font-medium text-gray-500">Method</dt>
                        <dd class="text-sm text-gray-900">{{ $log->method ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">URL</dt>
                        <dd class="text-sm text-gray-900 break-all">{{ $log->url ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500 mb-1">User Agent</dt>
                        <dd class="text-xs text-gray-600 break-all">{{ $log->user_agent ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Changes -->
            <div class="space-y-6">
                @if($log->old_values)
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">Previous Values</h2>
                        <pre class="bg-red-50 text-red-800 p-4 rounded text-xs overflow-x-auto max-h-64">{{ json_encode($log->old_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif

                @if($log->new_values)
                    <div class="bg-white rounded-lg shadow p-6">
                        <h2 class="text-lg font-semibold text-gray-800 mb-4">New Values</h2>
                        <pre class="bg-green-50 text-green-800 p-4 rounded text-xs overflow-x-auto max-h-64">{{ json_encode($log->new_values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </div>
                @endif

                @if(!$log->old_values && !$log->new_values)
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-gray-500 text-sm">No value changes recorded for this event.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.app>
