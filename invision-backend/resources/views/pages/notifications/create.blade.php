<x-layouts.app :title="'Send Notification'">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Send Notification</h1>
    </div>

    <div class="bg-white shadow sm:rounded-lg p-6">
        <form method="POST" action="{{ route('notifications.store') }}">
            @csrf

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Recipients</label>
                    <select name="user_ids[]" multiple required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" size="6">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple recipients.</p>
                    @error('user_ids') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Type</label>
                    <select name="type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        @foreach(\App\Enums\NotificationType::cases() as $t)
                            <option value="{{ $t->value }}">{{ $t->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Priority</label>
                    <select name="priority" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        @foreach(\App\Enums\NotificationPriority::cases() as $p)
                            <option value="{{ $p->value }}" @selected($p === \App\Enums\NotificationPriority::Normal)>{{ $p->label() }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Title</label>
                    <input type="text" name="title" value="{{ old('title') }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    @error('title') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700">Body</label>
                    <textarea name="body" rows="4"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('body') }}</textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('notifications.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Send Notification</button>
            </div>
        </form>
    </div>
</x-layouts.app>
