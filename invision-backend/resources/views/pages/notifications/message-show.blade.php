<x-layouts.app :title="$message->subject">
    <div class="mb-6">
        <a href="{{ route('messages.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Back to Messages</a>
    </div>

    <div class="bg-white shadow sm:rounded-lg p-6">
        <div class="flex items-start justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">{{ $message->subject }}</h1>
                <p class="mt-1 text-sm text-gray-500">
                    From: <span class="font-medium text-gray-700">{{ $message->sender?->name ?? 'Unknown' }}</span>
                    &middot; {{ $message->created_at->format('M d, Y \a\t H:i') }}
                </p>
            </div>
            @if($message->is_group)
                <span class="inline-flex rounded-full bg-purple-100 px-2 py-1 text-xs font-semibold text-purple-800">Group</span>
            @endif
        </div>

        <div class="mt-4 border-t pt-4">
            <h3 class="text-sm font-medium text-gray-700 mb-2">Recipients:</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($message->recipients as $recipient)
                    <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700">
                        {{ $recipient->user?->name ?? 'Unknown' }}
                        @if($recipient->read_at)
                            <span class="ml-1 text-green-500" title="Read {{ $recipient->read_at->format('M d, Y H:i') }}">✓</span>
                        @endif
                    </span>
                @endforeach
            </div>
        </div>

        <div class="mt-6 border-t pt-4">
            <div class="prose prose-sm max-w-none text-gray-700">
                {!! nl2br(e($message->body)) !!}
            </div>
        </div>
    </div>
</x-layouts.app>
