<x-layouts.app title="POS Transactions">
    <div class="flex items-center justify-between mb-6">
        <h1 class="text-2xl font-bold text-gray-900">POS Transactions</h1>
        <a href="{{ route('pos.transaction-create') }}" class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500">
            New Transaction
        </a>
    </div>

    <form method="GET" action="{{ route('pos.transactions') }}" class="mb-6 grid grid-cols-1 md:grid-cols-6 gap-4">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Transaction #..."
            class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        <select name="type" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="">All Types</option>
            @foreach(\App\Enums\PosTransactionType::cases() as $type)
                <option value="{{ $type->value }}" {{ request('type') === $type->value ? 'selected' : '' }}>{{ $type->label() }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="">All Status</option>
            @foreach(\App\Enums\PosTransactionStatus::cases() as $status)
                <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
            @endforeach
        </select>
        <select name="store_id" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
            <option value="">All Stores</option>
            @foreach($stores as $store)
                <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>{{ $store->name }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
        <button type="submit" class="inline-flex items-center justify-center rounded-md bg-gray-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-gray-500">
            Filter
        </button>
    </form>

    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Transaction #</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Store</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Total</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">User</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($transactions as $txn)
                <tr>
                    <td class="whitespace-nowrap px-6 py-4 text-sm font-mono text-gray-900">
                        <a href="{{ route('pos.transaction-show', $txn) }}" class="text-indigo-600 hover:text-indigo-900">{{ $txn->transaction_number }}</a>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $txn->store?->name }}</td>
                    <td class="whitespace-nowrap px-6 py-4">
                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-{{ $txn->type->color() }}-100 text-{{ $txn->type->color() }}-800">
                            {{ $txn->type->label() }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4">
                        <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-{{ $txn->status->color() }}-100 text-{{ $txn->status->color() }}-800">
                            {{ $txn->status->label() }}
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-right text-gray-900">${{ number_format($txn->total_amount, 2) }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $txn->user?->first_name }} {{ $txn->user?->last_name }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ $txn->created_at?->format('M d, Y') }}</td>
                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm">
                        <a href="{{ route('pos.transaction-show', $txn) }}" class="text-indigo-600 hover:text-indigo-900">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="px-6 py-12 text-center text-sm text-gray-500">No transactions found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $transactions->links() }}</div>
</x-layouts.app>
