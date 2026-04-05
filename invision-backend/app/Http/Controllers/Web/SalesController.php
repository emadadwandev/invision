<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\CreateRebateRequest;
use App\Http\Requests\Sales\CreateSalesOrderRequest;
use App\Http\Requests\Sales\RecordPaymentRequest;
use App\Http\Requests\Sales\UpdateRebateRequest;
use App\Http\Requests\Sales\UpdateSalesOrderRequest;
use App\Models\CreditAccount;
use App\Models\Product;
use App\Models\Rebate;
use App\Models\SalesOrder;
use App\Models\Store;
use App\Services\SalesService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalesController extends Controller
{
    public function __construct(
        private readonly SalesService $salesService,
    ) {}

    // ─── Sales Orders ─────────────────────────────────────────

    public function index(Request $request): View
    {
        $this->authorize('viewAny', SalesOrder::class);

        $orders = $this->salesService->listOrders(
            $request->only(['search', 'status', 'store_id', 'date_from', 'date_to']),
            $request->integer('per_page', 15)
        );

        return view('pages.sales.index', compact('orders'));
    }

    public function create(): View
    {
        $this->authorize('create', SalesOrder::class);

        $stores = Store::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        return view('pages.sales.create', compact('stores', 'products'));
    }

    public function store(CreateSalesOrderRequest $request): RedirectResponse
    {
        $this->authorize('create', SalesOrder::class);

        $data = [
            'store_id' => $request->validated('store_id'),
            'user_id' => $request->user()->id,
            'tenant_id' => $request->user()->tenant_id,
            'notes' => $request->validated('notes'),
        ];

        $this->salesService->createOrder($data, $request->validated('items'));

        return redirect()->route('sales.index')
            ->with('success', 'Sales order created successfully.');
    }

    public function show(SalesOrder $salesOrder): View
    {
        $this->authorize('view', $salesOrder);

        $salesOrder->load(['store', 'salesperson', 'items.product', 'payments.collector']);

        return view('pages.sales.show', compact('salesOrder'));
    }

    public function edit(SalesOrder $salesOrder): View
    {
        $this->authorize('update', $salesOrder);

        $salesOrder->load(['items.product']);
        $stores = Store::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();

        return view('pages.sales.edit', compact('salesOrder', 'stores', 'products'));
    }

    public function update(UpdateSalesOrderRequest $request, SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('update', $salesOrder);

        $this->salesService->updateOrder($salesOrder, $request->validated());

        return redirect()->route('sales.show', $salesOrder)
            ->with('success', 'Sales order updated successfully.');
    }

    public function destroy(SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('delete', $salesOrder);

        $this->salesService->deleteOrder($salesOrder);

        return redirect()->route('sales.index')
            ->with('success', 'Sales order deleted successfully.');
    }

    public function confirm(SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('update', $salesOrder);

        $this->salesService->confirmOrder($salesOrder);

        return redirect()->route('sales.show', $salesOrder)
            ->with('success', 'Order confirmed successfully.');
    }

    public function deliver(SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('update', $salesOrder);

        $this->salesService->deliverOrder($salesOrder);

        return redirect()->route('sales.show', $salesOrder)
            ->with('success', 'Order marked as delivered.');
    }

    public function cancel(SalesOrder $salesOrder): RedirectResponse
    {
        $this->authorize('update', $salesOrder);

        $this->salesService->cancelOrder($salesOrder);

        return redirect()->route('sales.show', $salesOrder)
            ->with('success', 'Order cancelled successfully.');
    }

    // ─── Payments ─────────────────────────────────────────────

    public function payments(Request $request): View
    {
        $payments = $this->salesService->listPayments(
            $request->only(['status', 'payment_method']),
            $request->integer('per_page', 15)
        );

        return view('pages.sales.payments', compact('payments'));
    }

    public function recordPayment(RecordPaymentRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['tenant_id'] = $request->user()->tenant_id;

        $this->salesService->recordPayment($data);

        return redirect()->route('sales.payments')
            ->with('success', 'Payment recorded successfully.');
    }

    // ─── Credit Accounts ──────────────────────────────────────

    public function creditAccounts(Request $request): View
    {
        $accounts = $this->salesService->listCreditAccounts(
            $request->only(['search']),
            $request->integer('per_page', 15)
        );

        return view('pages.sales.credit-accounts', compact('accounts'));
    }

    public function showCreditAccount(CreditAccount $creditAccount): View
    {
        $creditAccount->load(['store', 'transactions']);

        return view('pages.sales.credit-account-show', compact('creditAccount'));
    }

    // ─── Rebates ──────────────────────────────────────────────

    public function rebates(Request $request): View
    {
        $rebates = $this->salesService->listRebates(
            $request->only(['search', 'is_active']),
            $request->integer('per_page', 15)
        );

        return view('pages.sales.rebates', compact('rebates'));
    }

    public function createRebate(): View
    {
        $products = Product::where('is_active', true)->get();

        return view('pages.sales.rebate-create', compact('products'));
    }

    public function storeRebate(CreateRebateRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = $request->user()->tenant_id;

        $this->salesService->createRebate($data);

        return redirect()->route('sales.rebates')
            ->with('success', 'Rebate created successfully.');
    }

    public function editRebate(Rebate $rebate): View
    {
        $rebate->load(['product', 'category']);
        $products = Product::where('is_active', true)->get();

        return view('pages.sales.rebate-edit', compact('rebate', 'products'));
    }

    public function updateRebate(UpdateRebateRequest $request, Rebate $rebate): RedirectResponse
    {
        $this->salesService->updateRebate($rebate, $request->validated());

        return redirect()->route('sales.rebates')
            ->with('success', 'Rebate updated successfully.');
    }

    public function destroyRebate(Rebate $rebate): RedirectResponse
    {
        $this->salesService->deleteRebate($rebate);

        return redirect()->route('sales.rebates')
            ->with('success', 'Rebate deleted successfully.');
    }
}
