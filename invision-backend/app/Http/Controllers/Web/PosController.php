<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Pos\CreatePosTerminalRequest;
use App\Http\Requests\Pos\CreatePosTransactionRequest;
use App\Http\Requests\Pos\RecordStockMovementRequest;
use App\Http\Requests\Pos\UpdateInventoryRequest;
use App\Http\Requests\Pos\UpdatePosTerminalRequest;
use App\Models\PosTerminal;
use App\Models\PosTransaction;
use App\Models\Product;
use App\Models\Store;
use App\Models\StoreInventory;
use App\Services\PosService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PosController extends Controller
{
    public function __construct(
        private readonly PosService $posService,
    ) {}

    // ─── POS Terminals ────────────────────────────────────────

    public function terminals(Request $request): View
    {
        $this->authorize('viewAny', PosTerminal::class);
        $terminals = $this->posService->listTerminals(
            $request->only(['search', 'store_id', 'is_active']),
        );
        $stores = Store::where('is_active', true)->get();
        return view('pages.pos.terminals', compact('terminals', 'stores'));
    }

    public function createTerminal(): View
    {
        $this->authorize('create', PosTerminal::class);
        $stores = Store::where('is_active', true)->get();
        return view('pages.pos.terminal-create', compact('stores'));
    }

    public function storeTerminal(CreatePosTerminalRequest $request): RedirectResponse
    {
        $this->authorize('create', PosTerminal::class);
        $data = $request->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $this->posService->createTerminal($data);
        return redirect()->route('pos.terminals')
            ->with('success', 'POS terminal created successfully.');
    }

    public function editTerminal(PosTerminal $posTerminal): View
    {
        $this->authorize('update', $posTerminal);
        $stores = Store::where('is_active', true)->get();
        return view('pages.pos.terminal-edit', compact('posTerminal', 'stores'));
    }

    public function updateTerminal(UpdatePosTerminalRequest $request, PosTerminal $posTerminal): RedirectResponse
    {
        $this->authorize('update', $posTerminal);
        $this->posService->updateTerminal($posTerminal, $request->validated());
        return redirect()->route('pos.terminals')
            ->with('success', 'POS terminal updated successfully.');
    }

    public function destroyTerminal(PosTerminal $posTerminal): RedirectResponse
    {
        $this->authorize('delete', $posTerminal);
        $this->posService->deleteTerminal($posTerminal);
        return redirect()->route('pos.terminals')
            ->with('success', 'POS terminal deleted successfully.');
    }

    // ─── POS Transactions ─────────────────────────────────────

    public function transactions(Request $request): View
    {
        $transactions = $this->posService->listTransactions(
            $request->only(['search', 'type', 'status', 'store_id', 'date_from', 'date_to']),
        );
        $stores = Store::where('is_active', true)->get();
        return view('pages.pos.transactions', compact('transactions', 'stores'));
    }

    public function createTransaction(): View
    {
        $stores = Store::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        $terminals = PosTerminal::where('is_active', true)->get();
        return view('pages.pos.transaction-create', compact('stores', 'products', 'terminals'));
    }

    public function storeTransaction(CreatePosTransactionRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['user_id'] = $request->user()->id;
        $items = $data['items'];
        unset($data['items']);
        $this->posService->createTransaction($data, $items);
        return redirect()->route('pos.transactions')
            ->with('success', 'POS transaction created successfully.');
    }

    public function showTransaction(PosTransaction $posTransaction): View
    {
        $posTransaction->load(['store', 'user', 'terminal', 'items.product']);
        return view('pages.pos.transaction-show', compact('posTransaction'));
    }

    public function completeTransaction(PosTransaction $posTransaction): RedirectResponse
    {
        $this->posService->completeTransaction($posTransaction);
        return redirect()->route('pos.transaction-show', $posTransaction)
            ->with('success', 'Transaction completed. Inventory updated.');
    }

    public function voidTransaction(PosTransaction $posTransaction): RedirectResponse
    {
        $this->posService->voidTransaction($posTransaction);
        return redirect()->route('pos.transaction-show', $posTransaction)
            ->with('success', 'Transaction voided.');
    }

    // ─── Inventory ────────────────────────────────────────────

    public function inventory(Request $request): View
    {
        $inventory = $this->posService->listInventory(
            $request->only(['store_id', 'search', 'low_stock']),
        );
        $stores = Store::where('is_active', true)->get();
        return view('pages.pos.inventory', compact('inventory', 'stores'));
    }

    public function updateInventory(UpdateInventoryRequest $request, StoreInventory $storeInventory): RedirectResponse
    {
        $this->posService->updateInventoryCount($storeInventory, $request->validated());
        return redirect()->route('pos.inventory')
            ->with('success', 'Inventory count updated.');
    }

    // ─── Stock Movements ──────────────────────────────────────

    public function stockMovements(Request $request): View
    {
        $movements = $this->posService->listStockMovements(
            $request->only(['store_id', 'product_id', 'type', 'date_from', 'date_to']),
        );
        $stores = Store::where('is_active', true)->get();
        $products = Product::where('is_active', true)->get();
        return view('pages.pos.stock-movements', compact('movements', 'stores', 'products'));
    }

    public function recordStockMovement(RecordStockMovementRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['tenant_id'] = $request->user()->tenant_id;
        $data['user_id'] = $request->user()->id;
        $this->posService->recordStockMovement($data);
        return redirect()->route('pos.stock-movements')
            ->with('success', 'Stock movement recorded.');
    }
}
