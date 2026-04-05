<x-layouts.app title="Create Team">
    <div class="mb-6">
        <a href="{{ route('teams.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Back to Teams</a>
        <h1 class="mt-2 text-2xl font-bold text-gray-900">Create Team</h1>
    </div>

    <div class="bg-white shadow rounded-lg p-6">
        <form method="POST" action="{{ route('teams.store') }}" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Team Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm @error('name') border-red-500 @enderror">
                    @error('name') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="parent_team_id" class="block text-sm font-medium text-gray-700">Parent Team</label>
                    <select name="parent_team_id" id="parent_team_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        <option value="">None (top-level)</option>
                        @foreach($parentTeams as $parentTeam)
                            <option value="{{ $parentTeam->id }}" {{ old('parent_team_id') == $parentTeam->id ? 'selected' : '' }}>{{ $parentTeam->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="sm:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                    <textarea name="description" id="description" rows="3"
                              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">{{ old('description') }}</textarea>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('teams.index') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Cancel</a>
                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Create Team</button>
            </div>
        </form>
    </div>
</x-layouts.app>
