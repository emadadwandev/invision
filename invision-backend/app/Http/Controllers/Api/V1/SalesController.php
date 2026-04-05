<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sales\CreateRebateRequest;
use App\Http\Requests\Sales\CreateSalesOrderRequest;
use App\Http\Requests\Sales\RecordPaymentRequest;
use App\Http\Requests\Sales\UpdateRebateRequest;
use App\Http\Requests\Sales\UpdateSalesOrderRequest;
use App\Http\Resources\CreditAccountResource;
use App\Http\Resources\DepositReceiptResource;
use App\Http\Resources\PaymentResource;
use App\Http\Resources\RebateResource;
use App\Http\Resources\SalesOrderResource;
use App\Models\CreditAccount;
use App\Models\Rebate;
use App\Models\SalesOrder;
use App\Services\SalesService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SalesController extends Controller
{
    public function __construct(
        private readonly SalesService $salesService,
    ) {}

    // ─── Sales Orders ─────────────────────────────────────────

    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', SalesOrder::class);

        $orders = $this->salesService->listOrders(
            $request->only(['search', 'status', 'store_id', 'user_id', 'date_from', 'date_to']),
            $request->integer('per_page', 15)
        );

        return SalesOrderResource::collection($orders);
    }

    public function store(CreateSalesOrderRequest $request): JsonResponse
    {
        $this->authorize('create', SalesOrder::class);

        $data = [
            'store_id' => $request->validated('store_id'),
            'user_id' => $request->user()->id,
            'tenant_id' => $request->user()->tenant_id,
            'notes' => $request->validated('notes'),
        ];

        $order = $this->salesService->createOrder($data, $request->validated('items'));

        return (new SalesOrderResource($order))
            ->response()
            ->setStatusCode(201);
    }

    public function show(SalesOrder $salesOrder): SalesOrderResource
    {
        $this->authorize('view', $salesOrder);

        $salesOrder->load(['store', 'salesperson', 'items.product', 'payments.collector']);

        return new SalesOrderResource($salesOrder);
    }

    public function update(UpdateSalesOrderRequest $request, SalesOrder $salesOrder): SalesOrderResource
    {
        $this->authorize('update', $salesOrder);

        $order = $this->salesService->updateOrder($salesOrder, $request->validated());

        return new SalesOrderResource($order);
    }

    public function destroy(SalesOrder $salesOrder): JsonResponse
    {
        $this->authorize('delete', $salesOrder);

        $this->salesService->deleteOrder($salesOrder);

        return response()->json(null, 204);
    }

    public function confirm(SalesOrder $salesOrder): SalesOrderResource
    {
        $this->authorize('update', $salesOrder);

        $order = $this->salesService->confirmOrder($salesOrder);

        return new SalesOrderResource($order);
    }

    public function deliver(SalesOrder $salesOrder): SalesOrderResource
    {
        $this->authorize('update', $salesOrder);

        $order = $this->salesService->deliverOrder($salesOrder);

        return new SalesOrderResource($order);
    }

    public function cancel(SalesOrder $salesOrder): SalesOrderResource
    {
        $this->authorize('update', $salesOrder);

        $order = $this->salesService->cancelOrder($salesOrder);

        return new SalesOrderResource($order);
    }

    // ─── Order Items ──────────────────────────────────────────

    public function addItem(Request $request, SalesOrder $salesOrder): JsonResponse
    {
        $this->authorize('update', $salesOrder);

        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_price' => ['required', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'barcode_scanned' => ['nullable', 'string'],
        ]);

        $item = $this->salesService->addOrderItem($salesOrder, $validated);

        return response()->json(['data' => $item, 'message' => 'Item added successfully.'], 201);
    }

    public function removeItem(SalesOrder $salesOrder, int $itemId): JsonResponse
    {
        $this->authorize('update', $salesOrder);

        $item = $salesOrder->items()->findOrFail($itemId);
        $this->salesService->removeOrderItem($item);

        return response()->json(null, 204);
    }

    // ─── Payments ─────────────────────────────────────────────

    public function payments(Request $request): AnonymousResourceCollection
    {
        $payments = $this->salesService->listPayments(
            $request->only(['status', 'payment_method', 'sales_order_id']),
            $request->integer('per_page', 15)
        );

        return PaymentResource::collection($payments);
    }

    public function recordPayment(RecordPaymentRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $data['tenant_id'] = $request->user()->tenant_id;

        $payment = $this->salesService->recordPayment($data);

        return (new PaymentResource($payment))
            ->response()
            ->setStatusCode(201);
    }

    // ─── Credit Accounts ──────────────────────────────────────

    public function creditAccounts(Request $request): AnonymousResourceCollection
    {
        $accounts = $this->salesService->listCreditAccounts(
            $request->only(['search']),
            $request->integer('per_page', 15)
        );

        return CreditAccountResource::collection($accounts);
    }

    public function storeCreditAccount(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_id' => ['required', 'exists:stores,id', 'unique:credit_accounts,store_id'],
            'credit_limit' => ['required', 'numeric', 'min:0'],
        ]);

        $validated['tenant_id'] = $request->user()->tenant_id;

        $account = $this->salesService->createCreditAccount($validated);

        return (new CreditAccountResource($account->load('store')))
            ->response()
            ->setStatusCode(201);
    }

    public function updateCreditAccount(Request $request, CreditAccount $creditAccount): CreditAccountResource
    {
        $validated = $request->validate([
            'credit_limit' => ['required', 'numeric', 'min:0'],
        ]);

        $account = $this->salesService->updateCreditAccount($creditAccount, $validated);

        return new CreditAccountResource($account->load('store'));
    }

    public function creditPayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'store_id' => ['required', 'exists:stores,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_id' => ['nullable', 'exists:payments,id'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $transaction = $this->salesService->creditPayment(
            $validated['store_id'],
            $request->user()->tenant_id,
            $validated['amount'],
            $validated['payment_id'] ?? null,
            $validated['description'] ?? 'Credit payment received'
        );

        return response()->json(['data' => $transaction, 'message' => 'Credit payment recorded.'], 201);
    }

    // ─── Deposit Receipts ─────────────────────────────────────

    public function depositReceipts(Request $request): AnonymousResourceCollection
    {
        $receipts = $this->salesService->listDepositReceipts(
            $request->only(['search']),
            $request->integer('per_page', 15)
        );

        return DepositReceiptResource::collection($receipts);
    }

    public function storeDepositReceipt(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_id' => ['required', 'exists:payments,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'branch' => ['nullable', 'string', 'max:255'],
            'deposited_at' => ['nullable', 'date'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $data = [
            'tenant_id' => $request->user()->tenant_id,
            'user_id' => $request->user()->id,
            'payment_id' => $validated['payment_id'],
            'amount' => $validated['amount'],
            'bank_name' => $validated['bank_name'] ?? null,
            'branch' => $validated['branch'] ?? null,
            'deposited_at' => $validated['deposited_at'] ?? now(),
            'notes' => $validated['notes'] ?? null,
        ];

        if ($request->hasFile('photo')) {
            $data['photo_path'] = $request->file('photo')->store('deposit-receipts', 'public');
        }

        $receipt = $this->salesService->createDepositReceipt($data);

        return (new DepositReceiptResource($receipt))
            ->response()
            ->setStatusCode(201);
    }

    // ─── Rebates ──────────────────────────────────────────────

    public function rebates(Request $request): AnonymousResourceCollection
    {
        $rebates = $this->salesService->listRebates(
            $request->only(['search', 'is_active']),
            $request->integer('per_page', 15)
        );

        return RebateResource::collection($rebates);
    }

    public function storeRebate(CreateRebateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = $request->user()->tenant_id;

        $rebate = $this->salesService->createRebate($data);

        return (new RebateResource($rebate))
            ->response()
            ->setStatusCode(201);
    }

    public function showRebate(Rebate $rebate): RebateResource
    {
        $rebate->load(['product', 'category']);

        return new RebateResource($rebate);
    }

    public function updateRebate(UpdateRebateRequest $request, Rebate $rebate): RebateResource
    {
        $rebate = $this->salesService->updateRebate($rebate, $request->validated());

        return new RebateResource($rebate);
    }

    public function destroyRebate(Rebate $rebate): JsonResponse
    {
        $this->salesService->deleteRebate($rebate);

        return response()->json(null, 204);
    }

    // ─── Applicable Rebates ───────────────────────────────────

    public function applicableRebates(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'product_id' => ['required', 'exists:products,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $rebates = $this->salesService->getApplicableRebates(
            $validated['product_id'],
            $validated['quantity']
        );

        return response()->json(['data' => $rebates]);
    }

    // ─── My Orders (Mobile) ──────────────────────────────────

    public function myOrders(Request $request): AnonymousResourceCollection
    {
        $orders = $this->salesService->myOrders(
            $request->user()->id,
            $request->input('status')
        );

        return SalesOrderResource::collection($orders);
    }
}
