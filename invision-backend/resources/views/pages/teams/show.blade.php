<x-layouts.app title="Team Details">
    <div class="mb-6">
        <a href="{{ route('teams.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Back to Teams</a>
        <div class="mt-2 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">{{ $team->name }}</h1>
            <div class="flex space-x-3">
                @can('update', $team)
                <a href="{{ route('teams.edit', $team) }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Edit</a>
                @endcan
                @can('delete', $team)
                <form method="POST" action="{{ route('teams.destroy', $team) }}" onsubmit="return confirm('Are you sure?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50">Delete</button>
                </form>
                @endcan
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        {{-- Team Info --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Team Information</h3>
            </div>
            <div class="px-6 py-5">
                <dl class="grid grid-cols-1 gap-y-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Name</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $team->name }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Parent Team</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            @if($team->parentTeam)
                                <a href="{{ route('teams.show', $team->parentTeam) }}" class="text-indigo-600 hover:text-indigo-900">{{ $team->parentTeam->name }}</a>
                            @else
                                — (Top-level)
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Description</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $team->description ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $team->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $team->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </dd>
                    </div>
                </dl>
            </div>
        </div>

        {{-- Members --}}
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Members ({{ $team->members->count() }})</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @forelse($team->members as $member)
                <div class="px-6 py-4 flex items-center justify-between">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center">
                            <span class="text-sm font-medium text-white">{{ substr($member->first_name, 0, 1) }}</span>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-gray-900">{{ $member->full_name }}</p>
                            <p class="text-xs text-gray-500">{{ $member->role->label() }}</p>
                        </div>
                    </div>
                    <span class="text-xs text-gray-400">{{ $member->pivot->position }}</span>
                </div>
                @empty
                <div class="px-6 py-8 text-center text-sm text-gray-500">No members yet.</div>
                @endforelse
            </div>
        </div>

        {{-- Child Teams --}}
        @if($team->childTeams->count())
        <div class="bg-white shadow rounded-lg overflow-hidden lg:col-span-2">
            <div class="px-6 py-5 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Sub-Teams ({{ $team->childTeams->count() }})</h3>
            </div>
            <div class="divide-y divide-gray-200">
                @foreach($team->childTeams as $child)
                <div class="px-6 py-4">
                    <a href="{{ route('teams.show', $child) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">{{ $child->name }}</a>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</x-layouts.app>
