<x-layouts.app :title="'Messages'">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Messages</h1>
        <a href="{{ route('messages.compose') }}"
           class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
            Compose Message
        </a>
    </div>

    {{-- Search --}}
    <form method="GET" class="mb-6 flex gap-4 items-end">
        <div class="flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search messages..."
                   class="block w-full rounded-md border-gray-300 shadow-sm text-sm">
        </div>
        <button type="submit" class="rounded-md bg-gray-800 px-3 py-2 text-sm text-white hover:bg-gray-700">Search</button>
        <a href="{{ route('messages.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Reset</a>
    </form>

    {{-- Table --}}
    <div class="overflow-hidden bg-white shadow sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sender</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recipients</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($messages as $msg)
                    <tr>
                        <td class="px-6 py-4 text-sm font-medium">
                            <a href="{{ route('messages.show', $msg) }}" class="text-indigo-600 hover:text-indigo-900">
                                {{ $msg->subject }}
                            </a>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $msg->sender?->name ?? '-' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $msg->recipients->map(fn($r) => $r->user?->name)->filter()->implode(', ') }}
                        </td>
                        <td class="px-6 py-4 text-sm">
                            @if($msg->is_group)
                                <span class="inline-flex rounded-full bg-purple-100 px-2 text-xs font-semibold text-purple-800">Group</span>
                            @else
                                <span class="inline-flex rounded-full bg-blue-100 px-2 text-xs font-semibold text-blue-800">Direct</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $msg->created_at->format('M d, Y H:i') }}</td>
                        <td class="px-6 py-4 text-right text-sm">
                            <a href="{{ route('messages.show', $msg) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                            <form method="POST" action="{{ route('messages.destroy', $msg) }}" class="inline ml-2"
                                  onsubmit="return confirm('Delete this message?')">
                                @csrf @method('DELETE')
                                <button class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">No messages found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $messages->links() }}</div>
</x-layouts.app>
