<x-layouts.app :title="'Credit Account - ' . $creditAccount->store->name">
    <div class="mb-6">
        <a href="{{ route('sales.credit-accounts') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">&larr; Back to Credit Accounts</a>
        <h1 class="text-2xl font-semibold text-gray-900 mt-2">{{ $creditAccount->store->name }} — Credit Account</h1>
    </div>

    {{-- Account Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white shadow rounded-lg p-4">
            <p class="text-sm text-gray-500">Credit Limit</p>
            <p class="text-2xl font-bold text-gray-900">${{ number_format($creditAccount->credit_limit, 2) }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-4">
            <p class="text-sm text-gray-500">Current Balance</p>
            <p class="text-2xl font-bold {{ $creditAccount->current_balance > 0 ? 'text-red-600' : 'text-green-600' }}">${{ number_format($creditAccount->current_balance, 2) }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-4">
            <p class="text-sm text-gray-500">Available Credit</p>
            <p class="text-2xl font-bold text-green-600">${{ number_format($creditAccount->availableCredit(), 2) }}</p>
        </div>
        <div class="bg-white shadow rounded-lg p-4">
            <p class="text-sm text-gray-500">Last Payment</p>
            <p class="text-lg font-medium text-gray-900">{{ $creditAccount->last_payment_at?->format('M d, Y') ?? 'N/A' }}</p>
        </div>
    </div>

    {{-- Utilization Bar --}}
    @php
        $utilization = $creditAccount->credit_limit > 0 ? ($creditAccount->current_balance / $creditAccount->credit_limit) * 100 : 0;
        $barColor = $utilization > 80 ? 'bg-red-500' : ($utilization > 50 ? 'bg-yellow-500' : 'bg-green-500');
    @endphp
    <div class="bg-white shadow rounded-lg p-4 mb-6">
        <div class="flex justify-between text-sm mb-1">
            <span class="text-gray-500">Credit Utilization</span>
            <span class="font-medium">{{ number_format($utilization, 1) }}%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div class="{{ $barColor }} h-3 rounded-full" style="width: {{ min($utilization, 100) }}%"></div>
        </div>
    </div>

    {{-- Transaction History --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Transaction History</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Description</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Balance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($creditAccount->transactions->sortByDesc('created_at') as $txn)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $txn->created_at->format('M d, Y H:i') }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $txn->type === 'debit' ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800' }}">
                            {{ ucfirst($txn->type) }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $txn->description ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm font-medium {{ $txn->type === 'debit' ? 'text-red-600' : 'text-green-600' }}">
                        {{ $txn->type === 'debit' ? '+' : '-' }}${{ number_format($txn->amount, 2) }}
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">${{ number_format($txn->balance_after, 2) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-6 py-12 text-center text-sm text-gray-500">No transactions yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.app>
