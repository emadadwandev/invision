<x-layouts.app :title="'Task Assignments'">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Task Assignments</h1>
        <a href="{{ route('task-assignments.create') }}"
           class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
            Assign Task
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mb-6 flex flex-wrap gap-4 items-end">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tasks..."
                   class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
        <div>
            <select name="status" class="block rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">All Status</option>
                @foreach(\App\Enums\TaskAssignmentStatus::cases() as $s)
                    <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <select name="priority" class="block rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">All Priorities</option>
                @foreach(\App\Enums\NotificationPriority::cases() as $p)
                    <option value="{{ $p->value }}" @selected(request('priority') === $p->value)>{{ $p->label() }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-md bg-gray-800 px-3 py-2 text-sm text-white hover:bg-gray-700">Filter</button>
        <a href="{{ route('task-assignments.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Reset</a>
    </form>

    {{-- Table --}}
    <div class="overflow-hidden bg-white shadow sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assigned To</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Created</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($tasks as $task)
                    <tr class="{{ $task->isOverdue() ? 'bg-red-50' : '' }}">
                        <td class="px-6 py-4 text-sm font-medium">
                            <a href="{{ route('task-assignments.show', $task) }}" class="text-indigo-600 hover:text-indigo-900">
                                {{ $task->title }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $task->assignee?->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-{{ $task->priority->color() }}-100 text-{{ $task->priority->color() }}-800">
                                {{ $task->priority->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-{{ $task->status->color() }}-100 text-{{ $task->status->color() }}-800">
                                {{ $task->status->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $task->due_date?->format('M d, Y') ?? '-' }}
                            @if($task->isOverdue())
                                <span class="text-red-600 text-xs font-semibold">Overdue</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $task->created_at->format('M d, Y') }}</td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="{{ route('task-assignments.show', $task) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No task assignments found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $tasks->links() }}</div>
</x-layouts.app>
