<x-layouts.app title="POS Terminals">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">POS Terminals</h1>
        <a href="{{ route('pos.terminal-create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            Add Terminal
        </a>
    </div>

    <form method="GET" action="{{ route('pos.terminals') }}" class="mb-6 grid grid-cols-1 md:grid-cols-4 gap-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search terminals..."
            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        <select name="store_id" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="">All Stores</option>
            @foreach($stores as $store)
                <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
            @endforeach
        </select>
        <select name="is_active" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="">All Status</option>
            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
        </select>
        <button type="submit" class="inline-flex items-center justify-center rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
            Filter
        </button>
    </form>

    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Code</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Store</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Last Sync</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($terminals as $terminal)
                <tr>
                    <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-900">{{ $terminal->terminal_code }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-900">{{ $terminal->name }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $terminal->store?->name }}</td>
                    <td class="whitespace-nowrap px-6 py-4">
                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $terminal->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            {{ $terminal->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $terminal->last_synced_at?->diffForHumans() ?? 'Never' }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                        <a href="{{ route('pos.terminal-edit', $terminal) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">Edit</a>
                        <form method="POST" action="{{ route('pos.terminal-destroy', $terminal) }}" class="inline" onsubmit="return confirm('Delete this terminal?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No terminals found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $terminals->links() }}</div>
</x-layouts.app>
