<x-layouts.app :title="$taskAssignment->title">
    <div class="mb-6">
        <a href="{{ route('task-assignments.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Back to Tasks</a>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Main Info --}}
        <div class="lg:col-span-2 bg-white shadow sm:rounded-lg p-6">
            <div class="flex items-start justify-between">
                <h1 class="text-xl font-semibold text-gray-900">{{ $taskAssignment->title }}</h1>
                <div class="flex gap-2">
                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold bg-{{ $taskAssignment->priority->color() }}-100 text-{{ $taskAssignment->priority->color() }}-800">
                        {{ $taskAssignment->priority->label() }}
                    </span>
                    <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold bg-{{ $taskAssignment->status->color() }}-100 text-{{ $taskAssignment->status->color() }}-800">
                        {{ $taskAssignment->status->label() }}
                    </span>
                </div>
            </div>

            @if($taskAssignment->description)
                <div class="mt-4 prose prose-sm max-w-none text-gray-700">
                    {!! nl2br(e($taskAssignment->description)) !!}
                </div>
            @endif

            @if($taskAssignment->completion_notes)
                <div class="mt-4 border-t pt-4">
                    <h3 class="text-sm font-medium text-gray-700">Completion Notes</h3>
                    <p class="mt-1 text-sm text-gray-600">{{ $taskAssignment->completion_notes }}</p>
                </div>
            @endif

            @if($taskAssignment->proof_photo_path)
                <div class="mt-4 border-t pt-4">
                    <h3 class="text-sm font-medium text-gray-700">Proof of Completion</h3>
                    <img src="{{ $taskAssignment->proof_photo_path }}" alt="Proof photo" class="mt-2 max-w-md rounded-lg shadow">
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="bg-white shadow sm:rounded-lg p-6 space-y-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">Assigned By</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $taskAssignment->assigner?->name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Assigned To</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $taskAssignment->assignee?->name ?? '-' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Due Date</dt>
                <dd class="mt-1 text-sm {{ $taskAssignment->isOverdue() ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                    {{ $taskAssignment->due_date?->format('M d, Y') ?? 'No due date' }}
                    @if($taskAssignment->isOverdue()) (Overdue) @endif
                </dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Created</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $taskAssignment->created_at->format('M d, Y H:i') }}</dd>
            </div>
            @if($taskAssignment->completed_at)
                <div>
                    <dt class="text-sm font-medium text-gray-500">Completed</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $taskAssignment->completed_at->format('M d, Y H:i') }}</dd>
                </div>
            @endif

            {{-- Actions --}}
            @if($taskAssignment->status === \App\Enums\TaskAssignmentStatus::Completed)
                <div class="border-t pt-4 space-y-2">
                    <form method="POST" action="{{ route('task-assignments.verify', $taskAssignment) }}">
                        @csrf
                        <button type="submit" class="w-full rounded-md bg-green-600 px-3 py-2 text-sm font-semibold text-white hover:bg-green-500">
                            Verify Task
                        </button>
                    </form>
                    <div x-data="{ showReject: false }">
                        <button @click="showReject = !showReject" class="w-full rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-500">
                            Reject Task
                        </button>
                        <form method="POST" action="{{ route('task-assignments.reject', $taskAssignment) }}" x-show="showReject" x-cloak class="mt-2">
                            @csrf
                            <textarea name="reason" rows="2" placeholder="Reason for rejection..."
                                      class="block w-full rounded-md border-gray-300 shadow-sm text-sm"></textarea>
                            <button type="submit" class="mt-2 w-full rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white hover:bg-red-500">
                                Confirm Reject
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="border-t pt-4">
                <form method="POST" action="{{ route('task-assignments.destroy', $taskAssignment) }}"
                      onsubmit="return confirm('Delete this task?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="text-sm text-red-600 hover:text-red-900">Delete Task</button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.app>
