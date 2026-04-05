<x-layouts.app :title="'Credit Accounts'">
    <div class="mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Credit Accounts</h1>
    </div>

    {{-- Filters --}}
    <div class="bg-white shadow rounded-lg mb-6 p-4">
        <form method="GET" action="{{ route('sales.credit-accounts') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search store..." class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Search</button>
                <a href="{{ route('sales.credit-accounts') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Reset</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Store</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Credit Limit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Current Balance</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Available Credit</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Last Payment</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($accounts as $account)
                <tr>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $account->store->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">${{ number_format($account->credit_limit, 2) }}</td>
                    <td class="px-6 py-4 text-sm font-medium {{ $account->current_balance > 0 ? 'text-red-600' : 'text-green-600' }}">${{ number_format($account->current_balance, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">${{ number_format($account->availableCredit(), 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $account->last_payment_at?->format('M d, Y') ?? 'N/A' }}</td>
                    <td class="px-6 py-4 text-right text-sm">
                        <a href="{{ route('sales.credit-account-show', $account) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-sm text-gray-500">No credit accounts found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $accounts->withQueryString()->links() }}
        </div>
    </div>
</x-layouts.app>
