<x-layouts.app title="User Details">
    <div class="mb-6">
        <a href="{{ route('users.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Back to Users</a>
        <div class="mt-2 flex items-center justify-between">
            <h1 class="text-2xl font-bold text-gray-900">{{ $user->full_name }}</h1>
            <div class="flex space-x-3">
                @can('update', $user)
                <a href="{{ route('users.edit', $user) }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Edit</a>
                @endcan
                @can('delete', $user)
                <form method="POST" action="{{ route('users.destroy', $user) }}" onsubmit="return confirm('Are you sure you want to delete this user?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50">Delete</button>
                </form>
                @endcan
            </div>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">User Information</h3>
        </div>
        <div class="px-6 py-5">
            <dl class="grid grid-cols-1 gap-x-4 gap-y-6 sm:grid-cols-2">
                <div>
                    <dt class="text-sm font-medium text-gray-500">Full Name</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->full_name }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Email</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->phone ?? '—' }}</dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Role</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                            {{ $user->role->label() }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                    <dd class="mt-1">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </dd>
                </div>
                <div>
                    <dt class="text-sm font-medium text-gray-500">Created</dt>
                    <dd class="mt-1 text-sm text-gray-900">{{ $user->created_at->format('M d, Y H:i') }}</dd>
                </div>
            </dl>
        </div>
    </div>
</x-layouts.app>
