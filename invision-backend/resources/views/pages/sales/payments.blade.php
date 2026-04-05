<x-layouts.app :title="'Payments'">
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
        <h1 class="text-2xl font-semibold text-gray-900">Payments</h1>
    </div>

    {{-- Filters --}}
    <div class="bg-white shadow rounded-lg mb-6 p-4">
        <form method="GET" action="{{ route('sales.payments') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <select name="status" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">All Statuses</option>
                    @foreach(App\Enums\PaymentStatus::cases() as $status)
                    <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <select name="payment_method" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                    <option value="">All Methods</option>
                    @foreach(App\Enums\PaymentMethod::cases() as $method)
                    <option value="{{ $method->value }}" {{ request('payment_method') === $method->value ? 'selected' : '' }}>{{ $method->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">Filter</button>
                <a href="{{ route('sales.payments') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">Reset</a>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Store</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Collector</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($payments as $payment)
                <tr>
                    <td class="px-6 py-4 text-sm">
                        <a href="{{ route('sales.show', $payment->salesOrder) }}" class="text-indigo-600 hover:text-indigo-900">{{ $payment->salesOrder->order_number }}</a>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">{{ $payment->salesOrder->store->name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $payment->payment_method->label() }}</td>
                    <td class="px-6 py-4 text-sm font-medium text-gray-900">${{ number_format($payment->amount, 2) }}</td>
                    <td class="px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-{{ $payment->status->color() }}-100 text-{{ $payment->status->color() }}-800">
                            {{ $payment->status->label() }}
                        </span>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $payment->collector->full_name }}</td>
                    <td class="px-6 py-4 text-sm text-gray-500">{{ $payment->paid_at?->format('M d, Y') ?? $payment->created_at->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-sm text-gray-500">No payments found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $payments->withQueryString()->links() }}
        </div>
    </div>
</x-layouts.app>
