<x-layouts.app :title="'Competitors'">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Competitors</h1>
        <div class="flex gap-3">
            <a href="{{ route('competitors.products') }}"
               class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Products
            </a>
            <a href="{{ route('competitors.observations') }}"
               class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Observations
            </a>
            <a href="{{ route('competitors.analysis') }}"
               class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                Analysis
            </a>
            <a href="{{ route('competitors.create') }}"
               class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
                + Add Competitor
            </a>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mb-6 flex gap-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search competitors..."
               class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm w-64">
        <select name="is_active" onchange="this.form.submit()" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="">All Status</option>
            <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
            <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
        </select>
        <button type="submit" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            Search
        </button>
    </form>

    {{-- Competitors Table --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Products</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Observations</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse ($competitors as $competitor)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $competitor->name }}</div>
                            @if ($competitor->description)
                                <div class="text-sm text-gray-500">{{ Str::limit($competitor->description, 60) }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $competitor->products_count ?? 0 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $competitor->observations_count ?? 0 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 {{ $competitor->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $competitor->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('competitors.show', $competitor) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                            <a href="{{ route('competitors.edit', $competitor) }}" class="text-gray-600 hover:text-gray-900 mr-3">Edit</a>
                            <form method="POST" action="{{ route('competitors.destroy', $competitor) }}" class="inline" onsubmit="return confirm('Delete this competitor?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No competitors found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $competitors->withQueryString()->links() }}
    </div>
</x-layouts.app>
