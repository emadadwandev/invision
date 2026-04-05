<x-layouts.app :title="'Compose Message'">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Compose Message</h1>
    </div>

    <div class="bg-white shadow sm:rounded-lg p-6">
        <form method="POST" action="{{ route('messages.send') }}">
            @csrf

            <div class="grid grid-cols-1 gap-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Recipients</label>
                    <select name="recipient_ids[]" multiple required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm" size="6">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Hold Ctrl/Cmd to select multiple recipients.</p>
                    @error('recipient_ids') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Subject</label>
                    <input type="text" name="subject" value="{{ old('subject') }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                    @error('subject') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Message Body</label>
                    <textarea name="body" rows="8" required
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('body') }}</textarea>
                    @error('body') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('messages.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Send Message</button>
            </div>
        </form>
    </div>
</x-layouts.app>
