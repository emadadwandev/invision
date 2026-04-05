<x-layouts.app title="Users">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Users</h1>
            <p class="mt-1 text-sm text-gray-600">Manage system users and their roles.</p>
        </div>
        @can('create', App\Models\User::class)
        <a href="{{ route('users.create') }}" class="mt-4 sm:mt-0 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
            + Add User
        </a>
        @endcan
    </div>

    {{-- Filters --}}
    <div class="bg-white shadow rounded-lg mb-6 p-4" x-data="{ showFilters: false }">
        <form method="GET" action="{{ route('users.index') }}" class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-[200px]">
                <label class="block text-sm font-medium text-gray-700">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Name or email..."
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div class="w-48">
                <label class="block text-sm font-medium text-gray-700">Role</label>
                <select name="role" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">All Roles</option>
                    @foreach(App\Enums\UserRole::cases() as $role)
                        @if(!(isset($isTeamLeader) && $isTeamLeader) || $role->isMobileUser())
                        <option value="{{ $role->value }}" {{ request('role') === $role->value ? 'selected' : '' }}>{{ $role->label() }}</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm hover:bg-gray-700">Filter</button>
            <a href="{{ route('users.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md text-sm hover:bg-gray-50">Reset</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($users as $user)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 h-8 w-8 rounded-full bg-indigo-600 flex items-center justify-center">
                                <span class="text-sm font-medium text-white">{{ substr($user->first_name, 0, 1) }}</span>
                            </div>
                            <div class="ml-4">
                                <a href="{{ route('users.show', $user) }}" class="text-sm font-medium text-gray-900 hover:text-indigo-600">{{ $user->full_name }}</a>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $user->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                            {{ $user->role->label() }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:text-indigo-900">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No users found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        @if($users->hasPages())
        <div class="px-6 py-3 border-t border-gray-200">
            {{ $users->withQueryString()->links() }}
        </div>
        @endif
    </div>
</x-layouts.app>
