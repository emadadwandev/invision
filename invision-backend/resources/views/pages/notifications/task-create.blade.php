<x-layouts.app :title="'Assign Task'">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Assign Task</h1>
    </div>

    <div class="bg-white shadow sm:rounded-lg p-6">
        <form method="POST" action="{{ route('task-assignments.store') }}">
            @csrf

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Assign To</label>
                    <select name="assigned_to" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                        <option value="">Select User</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected(old('assigned_to') == $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @error('assigned_to') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
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
                    <label class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" rows="4"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">{{ old('description') }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700">Due Date</label>
                    <input type="date" name="due_date" value="{{ old('due_date') }}"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm text-sm">
                </div>
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <a href="{{ route('task-assignments.index') }}" class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-gray-300 hover:bg-gray-50">Cancel</a>
                <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Assign Task</button>
            </div>
        </form>
    </div>
</x-layouts.app>
