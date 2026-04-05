<x-layouts.app title="Task Details">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <div class="flex items-center gap-2">
                <a href="{{ route('campaigns.tasks') }}" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-900">Task #{{ $task->id }}</h1>
                @php $tc = $task->status->color(); @endphp
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $tc }}-100 text-{{ $tc }}-800">
                    {{ $task->status->label() }}
                </span>
            </div>
            <p class="mt-1 text-sm text-gray-600">{{ $task->campaign->name }}</p>
        </div>
        <div class="flex gap-2 mt-4 sm:mt-0">
            @if($task->status->value === 'completed')
            <form method="POST" action="{{ route('campaigns.task-verify', $task) }}">
                @csrf
                <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-md text-sm font-medium hover:bg-green-700">Verify</button>
            </form>
            <button onclick="document.getElementById('rejectModal').classList.remove('hidden')"
                    class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md text-sm font-medium text-red-700 bg-white hover:bg-red-50">Reject</button>
            @endif
        </div>
    </div>

    {{-- Task Info --}}
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Task Information</h2>
        <dl class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">Campaign</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    <a href="{{ route('campaigns.show', $task->campaign) }}" class="text-indigo-600 hover:text-indigo-900">{{ $task->campaign->name }}</a>
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Store</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $task->store->name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Assigned To</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $task->assignedUser->full_name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Completed At</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $task->completed_at?->format('M d, Y H:i') ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Verified By</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $task->verifier?->full_name ?? '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Verified At</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $task->verified_at?->format('M d, Y H:i') ?? '—' }}</dd>
            </div>
        </dl>
        @if($task->instructions)
        <div class="mt-4 pt-4 border-t border-gray-200">
            <dt class="text-sm font-medium text-gray-500">Instructions</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $task->instructions }}</dd>
        </div>
        @endif
        @if($task->rejection_reason)
        <div class="mt-4 pt-4 border-t border-red-200 bg-red-50 -mx-6 -mb-6 px-6 pb-6 rounded-b-lg">
            <dt class="text-sm font-medium text-red-700">Rejection Reason</dt>
            <dd class="mt-1 text-sm text-red-800">{{ $task->rejection_reason }}</dd>
        </div>
        @endif
    </div>

    {{-- Photos --}}
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Photos ({{ $task->photos->count() }})</h2>
        @if($task->photos->isEmpty())
            <p class="text-sm text-gray-500">No photos uploaded.</p>
        @else
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @foreach($task->photos as $photo)
                <div class="relative">
                    <img src="{{ asset('storage/' . $photo->photo_path) }}" alt="{{ $photo->caption ?? 'Task photo' }}"
                         class="w-full h-32 object-cover rounded-lg">
                    <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent rounded-b-lg p-2">
                        <p class="text-xs text-white">{{ ucfirst($photo->type ?? 'proof') }}</p>
                        @if($photo->caption)
                        <p class="text-xs text-white/80">{{ $photo->caption }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Entries --}}
    <div class="bg-white shadow rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Entries ({{ $task->entries->count() }})</h2>
        @if($task->entries->isEmpty())
            <p class="text-sm text-gray-500">No entries recorded for this task.</p>
        @else
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($task->entries as $entry)
                    <tr>
                        <td class="px-4 py-2 text-sm text-gray-900">{{ ucfirst(str_replace('_', ' ', $entry->entry_type)) }}</td>
                        <td class="px-4 py-2 text-sm text-gray-500 font-mono">{{ $entry->code ?? '—' }}</td>
                        <td class="px-4 py-2 text-sm text-gray-500">{{ $entry->quantity ?? 1 }}</td>
                        <td class="px-4 py-2 text-sm text-gray-500">{{ $entry->created_at->format('M d, Y H:i') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- Reject Modal --}}
    <div id="rejectModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Reject Task</h3>
            <form method="POST" action="{{ route('campaigns.task-reject', $task) }}">
                @csrf
                <div class="mb-4">
                    <label for="reason" class="block text-sm font-medium text-gray-700">Rejection Reason *</label>
                    <textarea name="reason" id="reason" rows="3" required
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('rejectModal').classList.add('hidden')"
                            class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700 hover:bg-gray-50">Cancel</button>
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-md text-sm font-medium hover:bg-red-700">Reject</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.app>
