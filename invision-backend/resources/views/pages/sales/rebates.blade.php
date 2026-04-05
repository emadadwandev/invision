<x-layouts.app :title="'Rebates'">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Rebates</h1>
        <a href="{{ route('sales.rebate-create') }}" class="mt-3 sm:mt-0 inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
            Create Rebate
        </a>
    </div>

    {{-- Filters --}}
    <div class="bg-white shadow rounded-lg mb-6 p-4">
        <form method="GET" action="{{ route('sales.rebates') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search rebates..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div>
                <select name="is_active" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">All</option>
                    <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Filter</button>
                <a href="{{ route('sales.rebates') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Reset</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Value</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($rebates as $rebate)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">
                        {{ $rebate->name }}
                        @if($rebate->product)
                        <span class="text-xs text-gray-500 block">{{ $rebate->product->name }}</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ ucfirst($rebate->type) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                        {{ $rebate->type === 'percentage' ? $rebate->value . '%' : '$' . number_format($rebate->value, 2) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $rebate->start_date->format('M d') }} - {{ $rebate->end_date->format('M d, Y') }}</td>
                    <td class="px-6 py-4">
                        @if($rebate->isCurrentlyActive())
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">Active</span>
                        @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">Inactive</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 text-right text-sm font-medium">
                        <a href="{{ route('sales.rebate-edit', $rebate) }}" class="text-yellow-600 hover:text-yellow-900">Edit</a>
                        <form method="POST" action="{{ route('sales.rebate-destroy', $rebate) }}" class="inline ml-3">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('Delete this rebate?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No rebates found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $rebates->withQueryString()->links() }}
        </div>
    </div>
</x-layouts.app>
