<x-layouts.app :title="'Campaigns'">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Campaigns</h1>
        @can('create', App\Models\Campaign::class)
        <a href="{{ route('campaigns.create') }}" class="mt-3 sm:mt-0 inline-flex items-center px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-md hover:bg-indigo-700">
            + New Campaign
        </a>
        @endcan
    </div>

    {{-- Filters --}}
    <form method="GET" class="mb-6 grid grid-cols-1 sm:grid-cols-4 gap-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search campaigns..."
               class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
        <select name="status" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            @foreach(App\Enums\CampaignStatus::cases() as $s)
                <option value="{{ $s->value }}" {{ request('status') === $s->value ? 'selected' : '' }}>{{ $s->label() }}</option>
            @endforeach
        </select>
        <select name="type" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm" onchange="this.form.submit()">
            <option value="">All Types</option>
            @foreach(App\Enums\CampaignType::cases() as $t)
                <option value="{{ $t->value }}" {{ request('type') === $t->value ? 'selected' : '' }}>{{ $t->label() }}</option>
            @endforeach
        </select>
        <button type="submit" class="inline-flex justify-center items-center px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded-md hover:bg-gray-700">
            Filter
        </button>
    </form>

    {{-- Table --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Name</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dates</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Budget</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($campaigns as $campaign)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <a href="{{ route('campaigns.show', $campaign) }}" class="text-indigo-600 hover:text-indigo-900 font-medium">
                            {{ $campaign->name }}
                        </a>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $campaign->type->label() }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $campaign->status->color() }}-100 text-{{ $campaign->status->color() }}-800">
                            {{ $campaign->status->label() }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $campaign->start_date->format('M d') }} – {{ $campaign->end_date->format('M d, Y') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($campaign->budget)
                            ${{ number_format($campaign->budget, 2) }}
                        @else
                            —
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                        <a href="{{ route('campaigns.show', $campaign) }}" class="text-indigo-600 hover:text-indigo-900 mr-3">View</a>
                        @can('update', $campaign)
                        <a href="{{ route('campaigns.edit', $campaign) }}" class="text-yellow-600 hover:text-yellow-900 mr-3">Edit</a>
                        @endcan
                        @can('delete', $campaign)
                        <form method="POST" action="{{ route('campaigns.destroy', $campaign) }}" class="inline" onsubmit="return confirm('Delete this campaign?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900">Delete</button>
                        </form>
                        @endcan
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-gray-500">No campaigns found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $campaigns->withQueryString()->links() }}</div>
</x-layouts.app>
