<x-layouts.app :title="'Competitor Observations'">
    <div class="flex items-center justify-between mb-6">
        <div>
            <a href="{{ route('competitors.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900">&larr; Back to Competitors</a>
            <h1 class="text-2xl font-semibold text-gray-900 mt-1">Competitor Observations</h1>
        </div>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mb-6 flex flex-wrap gap-4">
        <select name="competitor_id" onchange="this.form.submit()" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="">All Competitors</option>
            @foreach ($competitors as $comp)
                <option value="{{ $comp->id }}" {{ request('competitor_id') == $comp->id ? 'selected' : '' }}>{{ $comp->name }}</option>
            @endforeach
        </select>
        <select name="store_id" onchange="this.form.submit()" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="">All Stores</option>
            @foreach ($stores as $store)
                <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
            @endforeach
        </select>
        <select name="observation_type" onchange="this.form.submit()" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="">All Types</option>
            @foreach (App\Enums\ObservationType::cases() as $type)
                <option value="{{ $type->value }}" {{ request('observation_type') === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
            @endforeach
        </select>
        <input type="date" name="from" value="{{ request('from') }}" onchange="this.form.submit()"
               class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        <input type="date" name="to" value="{{ request('to') }}" onchange="this.form.submit()"
               class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
    </form>

    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Store</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recorded By</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Competitor</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Notes</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse ($observations as $obs)
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $obs->observed_at?->format('M d, Y H:i') ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $obs->store?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $obs->user ? $obs->user->first_name . ' ' . $obs->user->last_name : '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $obs->competitor?->name ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $obs->competitorProduct?->name ?? '—' }}</td>
                        <td class="px-6 py-4">
                            <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-{{ $obs->observation_type->color() }}-100 text-{{ $obs->observation_type->color() }}-800">
                                {{ $obs->observation_type->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $obs->quantity ?? '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ $obs->price ? number_format($obs->price, 2) : '—' }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ Str::limit($obs->notes, 30) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-sm text-gray-500">No observations found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $observations->withQueryString()->links() }}
    </div>
</x-layouts.app>
