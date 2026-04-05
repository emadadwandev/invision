<x-layouts.app title="Transaction {{ $posTransaction->transaction_number }}">
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">{{ $posTransaction->transaction_number }}</h1>
            <div class="mt-1 flex items-center gap-2">
                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-{{ $posTransaction->type->color() }}-100 text-{{ $posTransaction->type->color() }}-800">
                    {{ $posTransaction->type->label() }}
                </span>
                <span class="inline-flex rounded-full px-2 text-xs font-semibold leading-5 bg-{{ $posTransaction->status->color() }}-100 text-{{ $posTransaction->status->color() }}-800">
                    {{ $posTransaction->status->label() }}
                </span>
            </div>
        </div>
        <div class="flex gap-2">
            @if($posTransaction->status === \App\Enums\PosTransactionStatus::Pending)
                <form method="POST" action="{{ route('pos.transaction-complete', $posTransaction) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500">Complete</button>
                </form>
                <form method="POST" action="{{ route('pos.transaction-void', $posTransaction) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500">Void</button>
                </form>
            @endif
        </div>
    </div>

    <!-- Info Grid -->
    <div class="bg-white shadow-sm sm:rounded-lg p-6 mb-6">
        <dl class="grid grid-cols-1 gap-x-4 gap-y-4 sm:grid-cols-3">
            <div>
                <dt class="text-sm font-medium text-gray-500">Store</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $posTransaction->store?->name }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Terminal</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $posTransaction->terminal?->name ?? 'N/A' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">User</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $posTransaction->user?->first_name }} {{ $posTransaction->user?->last_name }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Payment Method</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($posTransaction->payment_method ?? 'N/A') }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Synced At</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $posTransaction->synced_at?->format('M d, Y H:i') ?? 'Not yet' }}</dd>
            </div>
            <div>
                <dt class="text-sm font-medium text-gray-500">Created</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $posTransaction->created_at?->format('M d, Y H:i') }}</dd>
            </div>
        </dl>
        @if($posTransaction->notes)
        <div class="mt-4">
            <dt class="text-sm font-medium text-gray-500">Notes</dt>
            <dd class="mt-1 text-sm text-gray-900">{{ $posTransaction->notes }}</dd>
        </div>
        @endif
    </div>

    <!-- Items Table -->
    <div class="bg-white shadow-sm sm:rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Items</h3>
        </div>
        <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Barcode</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Qty</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Unit Price</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Discount</th>
                    <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Line Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @foreach($posTransaction->items as $item)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $item->product?->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500 font-mono">{{ $item->barcode_scanned ?? '-' }}</td>
                    <td class="px-6 py-4 text-sm text-right text-gray-900">{{ $item->quantity }}</td>
                    <td class="px-6 py-4 text-sm text-right text-gray-900">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-right text-gray-500">${{ number_format($item->discount_amount, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-right font-medium text-gray-900">${{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="5" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Subtotal</td>
                    <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">${{ number_format($posTransaction->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Tax</td>
                    <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">${{ number_format($posTransaction->tax_amount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="5" class="px-6 py-3 text-right text-sm font-bold text-gray-900">Total</td>
                    <td class="px-6 py-3 text-right text-sm font-bold text-gray-900">${{ number_format($posTransaction->total_amount, 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <a href="{{ route('pos.transactions') }}" class="text-indigo-600 hover:text-indigo-500 text-sm">&larr; Back to Transactions</a>
</x-layouts.app>
