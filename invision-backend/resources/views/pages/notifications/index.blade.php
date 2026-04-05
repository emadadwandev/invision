<x-layouts.app :title="'Notifications'">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Notifications</h1>
        <a href="{{ route('notifications.create') }}"
           class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
            Send Notification
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mb-6 flex flex-wrap gap-4 items-end">
        <div>
            <label class="block text-sm font-medium text-gray-700">Type</label>
            <select name="type" class="mt-1 block rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">All Types</option>
                @foreach(\App\Enums\NotificationType::cases() as $t)
                    <option value="{{ $t->value }}" @selected(request('type') === $t->value)>{{ $t->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Priority</label>
            <select name="priority" class="mt-1 block rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">All Priorities</option>
                @foreach(\App\Enums\NotificationPriority::cases() as $p)
                    <option value="{{ $p->value }}" @selected(request('priority') === $p->value)>{{ $p->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Status</label>
            <select name="is_read" class="mt-1 block rounded-md border-gray-300 shadow-sm text-sm">
                <option value="">All</option>
                <option value="0" @selected(request('is_read') === '0')>Unread</option>
                <option value="1" @selected(request('is_read') === '1')>Read</option>
            </select>
        </div>
        <button type="submit" class="rounded-md bg-gray-800 px-3 py-2 text-sm text-white hover:bg-gray-700">Filter</button>
        <a href="{{ route('notifications.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Reset</a>
    </form>

    {{-- Table --}}
    <div class="overflow-hidden bg-white shadow sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Title</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recipient</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($notifications as $notification)
                    <tr class="{{ $notification->isRead() ? '' : 'bg-blue-50' }}">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $notification->title }}</td>
                        <td class="px-6 py-4 text-sm">
                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-{{ $notification->type->color() }}-100 text-{{ $notification->type->color() }}-800">
                                {{ $notification->type->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm">
                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-{{ $notification->priority->color() }}-100 text-{{ $notification->priority->color() }}-800">
                                {{ $notification->priority->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $notification->user?->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm">
                            @if($notification->isRead())
                                <span class="text-green-600 text-xs">Read</span>
                            @else
                                <span class="text-yellow-600 text-xs font-semibold">Unread</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $notification->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 text-right text-sm">
                            <form method="POST" action="{{ route('notifications.destroy', $notification) }}" class="inline"
                                  onsubmit="return confirm('Delete this notification?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No notifications found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>
</x-layouts.app>
