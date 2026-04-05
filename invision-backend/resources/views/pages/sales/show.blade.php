<x-layouts.app :title="'Order ' . $salesOrder->order_number">
    <div class="mb-6">
        <a href="{{ route('sales.index') }}" class="text-indigo-600 hover:text-indigo-900 text-sm">&larr; Back to Sales Orders</a>
        <div class="flex items-center justify-between mt-2">
            <h1 class="text-2xl font-semibold text-gray-900">Order {{ $salesOrder->order_number }}</h1>
            <div class="flex gap-2">
                @can('update', $salesOrder)
                    @if($salesOrder->status === App\Enums\OrderStatus::Draft)
                    <form method="POST" action="{{ route('sales.confirm', $salesOrder) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md text-sm font-medium text-white bg-blue-600 hover:bg-blue-700">Confirm</button>
                    </form>
                    @endif
                    @if($salesOrder->status === App\Enums\OrderStatus::Confirmed)
                    <form method="POST" action="{{ route('sales.deliver', $salesOrder) }}">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md text-sm font-medium text-white bg-green-600 hover:bg-green-700">Mark Delivered</button>
                    </form>
                    @endif
                    @if(in_array($salesOrder->status, [App\Enums\OrderStatus::Draft, App\Enums\OrderStatus::Confirmed]))
                    <form method="POST" action="{{ route('sales.cancel', $salesOrder) }}" onsubmit="return confirm('Cancel this order?')">
                        @csrf
                        <button type="submit" class="inline-flex items-center px-3 py-1.5 border border-transparent rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700">Cancel Order</button>
                    </form>
                    @endif
                @endcan
            </div>
        </div>
    </div>

    {{-- Order Info --}}
    <div class="bg-white shadow rounded-lg p-6 mb-6">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
            <div>
                <p class="text-sm text-gray-500">Status</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $salesOrder->status->color() }}-100 text-{{ $salesOrder->status->color() }}-800">
                    {{ $salesOrder->status->label() }}
                </span>
            </div>
            <div>
                <p class="text-sm text-gray-500">Store</p>
                <p class="text-sm font-medium text-gray-900">{{ $salesOrder->store->name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Salesperson</p>
                <p class="text-sm font-medium text-gray-900">{{ $salesOrder->salesperson->full_name }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Created</p>
                <p class="text-sm font-medium text-gray-900">{{ $salesOrder->created_at->format('M d, Y H:i') }}</p>
            </div>
            @if($salesOrder->delivered_at)
            <div>
                <p class="text-sm text-gray-500">Delivered</p>
                <p class="text-sm font-medium text-gray-900">{{ $salesOrder->delivered_at->format('M d, Y H:i') }}</p>
            </div>
            @endif
            @if($salesOrder->notes)
            <div>
                <p class="text-sm text-gray-500">Notes</p>
                <p class="text-sm text-gray-900">{{ $salesOrder->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Order Items --}}
    <div class="bg-white shadow rounded-lg overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Order Items</h2>
        </div>
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Unit Price</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Discount</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Line Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($salesOrder->items as $item)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $item->product->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $item->quantity }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $item->discount_percent }}% (${{ number_format($item->discount_amount, 2) }})</td>
                    <td class="px-6 py-4 text-sm text-gray-900 text-right">${{ number_format($item->line_total, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot class="bg-gray-50">
                <tr>
                    <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Subtotal</td>
                    <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">${{ number_format($salesOrder->subtotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Discount</td>
                    <td class="px-6 py-3 text-right text-sm font-medium text-red-600">-${{ number_format($salesOrder->discount_amount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-gray-500">Tax</td>
                    <td class="px-6 py-3 text-right text-sm font-medium text-gray-900">${{ number_format($salesOrder->tax_amount, 2) }}</td>
                </tr>
                <tr class="border-t-2 border-gray-300">
                    <td colspan="4" class="px-6 py-3 text-right text-sm font-bold text-gray-900">Total</td>
                    <td class="px-6 py-3 text-right text-sm font-bold text-gray-900">${{ number_format($salesOrder->total_amount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="px-6 py-3 text-right text-sm font-medium text-green-600">Paid</td>
                    <td class="px-6 py-3 text-right text-sm font-medium text-green-600">${{ number_format($salesOrder->totalPaid(), 2) }}</td>
                </tr>
                <tr>
                    <td colspan="4" class="px-6 py-3 text-right text-sm font-bold text-red-600">Balance Due</td>
                    <td class="px-6 py-3 text-right text-sm font-bold text-red-600">${{ number_format($salesOrder->balanceDue(), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Payments --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Payments</h2>
        </div>
        @if($salesOrder->payments->count())
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Collector</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @foreach($salesOrder->payments as $payment)
                <tr>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $payment->payment_method->label() }}</td>
                    <td class="px-6 py-4 text-sm text-gray-900">${{ number_format($payment->amount, 2) }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $payment->status->color() }}-100 text-{{ $payment->status->color() }}-800">
                            {{ $payment->status->label() }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $payment->collector->full_name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $payment->paid_at?->format('M d, Y') ?? '-' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
        <div class="px-6 py-12 text-center text-sm text-gray-500">No payments recorded yet.</div>
        @endif
    </div>
</x-layouts.app>
