<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\CreatePosTerminalRequest;
use App\Http\Requests\Pos\CreatePosTransactionRequest;
use App\Http\Requests\Pos\RecordStockMovementRequest;
use App\Http\Requests\Pos\UpdateInventoryRequest;
use App\Http\Requests\Pos\UpdatePosTerminalRequest;
use App\Http\Resources\PosTerminalResource;
use App\Http\Resources\PosTransactionResource;
use App\Http\Resources\StockMovementResource;
use App\Http\Resources\StoreInventoryResource;
use App\Models\PosTerminal;
use App\Models\PosTransaction;
use App\Models\StoreInventory;
use App\Services\PosService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PosController extends Controller
{
    public function __construct(
        private readonly PosService $posService,
    ) {}

    // ─── POS Terminals ────────────────────────────────────────

    public function terminals(Request $request): AnonymousResourceCollection
    {
        $terminals = $this->posService->listTerminals(
            $request->only(['search', 'store_id', 'is_active']),
        );
        return PosTerminalResource::collection($terminals);
    }

    public function storeTerminal(CreatePosTerminalRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $terminal = $this->posService->createTerminal($data);
        return (new PosTerminalResource($terminal->load('store')))
            ->response()
            ->setStatusCode(201);
    }

    public function showTerminal(PosTerminal $posTerminal): PosTerminalResource
    {
        $this->authorize('view', $posTerminal);
        return new PosTerminalResource($posTerminal->load('store'));
    }

    public function updateTerminal(UpdatePosTerminalRequest $request, PosTerminal $posTerminal): PosTerminalResource
    {
        $this->authorize('update', $posTerminal);
        $terminal = $this->posService->updateTerminal($posTerminal, $request->validated());
        return new PosTerminalResource($terminal->load('store'));
    }

    public function destroyTerminal(PosTerminal $posTerminal): JsonResponse
    {
        $this->authorize('delete', $posTerminal);
        $this->posService->deleteTerminal($posTerminal);
        return response()->json(['message' => 'Terminal deleted.']);
    }

    public function syncTerminal(PosTerminal $posTerminal): PosTerminalResource
    {
        $terminal = $this->posService->syncTerminal($posTerminal);
        return new PosTerminalResource($terminal->load('store'));
    }

    // ─── POS Transactions ─────────────────────────────────────

    public function transactions(Request $request): AnonymousResourceCollection
    {
        $transactions = $this->posService->listTransactions(
            $request->only(['search', 'type', 'status', 'store_id', 'date_from', 'date_to']),
        );
        return PosTransactionResource::collection($transactions);
    }

    public function storeTransaction(CreatePosTransactionRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['user_id'] = $request->user()->id;
        $items = $data['items'];
        unset($data['items']);
        $transaction = $this->posService->createTransaction($data, $items);
        return (new PosTransactionResource($transaction))
            ->response()
            ->setStatusCode(201);
    }

    public function showTransaction(PosTransaction $posTransaction): PosTransactionResource
    {
        return new PosTransactionResource(
            $posTransaction->load(['store', 'user', 'terminal', 'items.product'])
        );
    }

    public function completeTransaction(PosTransaction $posTransaction): PosTransactionResource
    {
        $transaction = $this->posService->completeTransaction($posTransaction);
        return new PosTransactionResource($transaction);
    }

    public function voidTransaction(PosTransaction $posTransaction): PosTransactionResource
    {
        $transaction = $this->posService->voidTransaction($posTransaction);
        return new PosTransactionResource($transaction);
    }

    public function syncTransaction(PosTransaction $posTransaction): PosTransactionResource
    {
        $transaction = $this->posService->syncTransaction($posTransaction);
        return new PosTransactionResource($transaction);
    }

    // ─── Store Inventory ──────────────────────────────────────

    public function inventory(Request $request): AnonymousResourceCollection
    {
        $inventory = $this->posService->listInventory(
            $request->only(['store_id', 'search', 'low_stock']),
        );
        return StoreInventoryResource::collection($inventory);
    }

    public function storeInventory(int $storeId): AnonymousResourceCollection
    {
        $inventory = $this->posService->getStoreInventory($storeId);
        return StoreInventoryResource::collection($inventory);
    }

    public function updateInventory(UpdateInventoryRequest $request, StoreInventory $storeInventory): StoreInventoryResource
    {
        $inventory = $this->posService->updateInventoryCount($storeInventory, $request->validated());
        return new StoreInventoryResource($inventory);
    }

    // ─── Stock Movements ──────────────────────────────────────

    public function stockMovements(Request $request): AnonymousResourceCollection
    {
        $movements = $this->posService->listStockMovements(
            $request->only(['store_id', 'product_id', 'type', 'date_from', 'date_to']),
        );
        return StockMovementResource::collection($movements);
    }

    public function recordStockMovement(RecordStockMovementRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['user_id'] = $request->user()->id;
        $movement = $this->posService->recordStockMovement($data);
        return (new StockMovementResource($movement))
            ->response()
            ->setStatusCode(201);
    }

    // ─── Mobile ───────────────────────────────────────────────

    public function myTransactions(Request $request): AnonymousResourceCollection
    {
        $transactions = $this->posService->myTransactions(
            $request->user()->id,
            $request->query('type'),
        );
        return PosTransactionResource::collection($transactions);
    }
}
