<?php

namespace App\Services;

use App\Enums\PosTransactionStatus;
use App\Enums\PosTransactionType;
use App\Enums\StockMovementType;
use App\Models\PosTerminal;
use App\Models\PosTransaction;
use App\Models\PosTransactionItem;
use App\Models\StockMovement;
use App\Models\StoreInventory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PosService
{
    // ─── POS Terminals ────────────────────────────────────────

    public function listTerminals(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return PosTerminal::query()
            ->with('store')
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where('name', 'like', "%{$s}%")
                ->orWhere('terminal_code', 'like', "%{$s}%"))
            ->when(isset($filters['store_id']), fn ($q) => $q->where('store_id', $filters['store_id']))
            ->when(isset($filters['is_active']), fn ($q) => $q->where('is_active', $filters['is_active']))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function createTerminal(array $data): PosTerminal
    {
        return PosTerminal::create($data);
    }

    public function updateTerminal(PosTerminal $terminal, array $data): PosTerminal
    {
        $terminal->update($data);
        return $terminal->fresh();
    }

    public function deleteTerminal(PosTerminal $terminal): void
    {
        $terminal->delete();
    }

    public function syncTerminal(PosTerminal $terminal): PosTerminal
    {
        $terminal->update(['last_synced_at' => now()]);
        return $terminal->fresh();
    }

    // ─── POS Transactions ─────────────────────────────────────

    public function listTransactions(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return PosTransaction::query()
            ->with(['store', 'user', 'terminal'])
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->where('transaction_number', 'like', "%{$s}%"))
            ->when($filters['type'] ?? null, fn ($q, $t) => $q->where('type', $t))
            ->when($filters['status'] ?? null, fn ($q, $s) => $q->where('status', $s))
            ->when(isset($filters['store_id']), fn ($q) => $q->where('store_id', $filters['store_id']))
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function createTransaction(array $data, array $items): PosTransaction
    {
        return DB::transaction(function () use ($data, $items) {
            $number = 'POS-' . date('Ymd') . '-' . str_pad(
                PosTransaction::whereDate('created_at', today())->count() + 1,
                4, '0', STR_PAD_LEFT
            );

            $subtotal = 0;
            foreach ($items as &$item) {
                $discount = $item['discount_amount'] ?? 0;
                $lineTotal = ($item['quantity'] * $item['unit_price']) - $discount;
                $item['line_total'] = $lineTotal;
                $subtotal += $lineTotal;
            }

            $taxAmount = $data['tax_amount'] ?? round($subtotal * 0.0, 2);
            $totalAmount = $subtotal + $taxAmount;

            $transaction = PosTransaction::create([
                'tenant_id' => $data['tenant_id'],
                'store_id' => $data['store_id'],
                'pos_terminal_id' => $data['pos_terminal_id'] ?? null,
                'user_id' => $data['user_id'],
                'transaction_number' => $number,
                'type' => $data['type'] ?? PosTransactionType::SellOut->value,
                'status' => PosTransactionStatus::Pending->value,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'payment_method' => $data['payment_method'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($items as $item) {
                $transaction->items()->create($item);
            }

            return $transaction->load(['store', 'user', 'terminal', 'items.product']);
        });
    }

    public function completeTransaction(PosTransaction $transaction): PosTransaction
    {
        return DB::transaction(function () use ($transaction) {
            $transaction->update([
                'status' => PosTransactionStatus::Completed->value,
            ]);

            // Update inventory and create stock movements
            foreach ($transaction->items as $item) {
                $movementType = match ($transaction->type) {
                    PosTransactionType::SellOut => StockMovementType::SellOut,
                    PosTransactionType::SellThrough => StockMovementType::SellThrough,
                    PosTransactionType::Return => StockMovementType::Return,
                };

                $quantity = $transaction->type === PosTransactionType::Return
                    ? $item->quantity
                    : -$item->quantity;

                $this->adjustInventory(
                    $transaction->tenant_id,
                    $transaction->store_id,
                    $item->product_id,
                    $quantity,
                    $movementType,
                    'pos_transactions',
                    $transaction->id,
                    $transaction->user_id,
                );
            }

            return $transaction->fresh(['store', 'user', 'terminal', 'items.product']);
        });
    }

    public function voidTransaction(PosTransaction $transaction): PosTransaction
    {
        return DB::transaction(function () use ($transaction) {
            // Reverse inventory if it was completed
            if ($transaction->status === PosTransactionStatus::Completed) {
                foreach ($transaction->items as $item) {
                    $reverseQty = $transaction->type === PosTransactionType::Return
                        ? -$item->quantity
                        : $item->quantity;

                    $this->adjustInventory(
                        $transaction->tenant_id,
                        $transaction->store_id,
                        $item->product_id,
                        $reverseQty,
                        StockMovementType::Adjustment,
                        'pos_transactions',
                        $transaction->id,
                        $transaction->user_id,
                        'Voided transaction reversal',
                    );
                }
            }

            $transaction->update(['status' => PosTransactionStatus::Voided->value]);
            return $transaction->fresh(['store', 'user', 'terminal', 'items.product']);
        });
    }

    public function syncTransaction(PosTransaction $transaction): PosTransaction
    {
        $transaction->update([
            'status' => PosTransactionStatus::Synced->value,
            'synced_at' => now(),
        ]);
        return $transaction->fresh();
    }

    // ─── Store Inventory ──────────────────────────────────────

    public function listInventory(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return StoreInventory::query()
            ->with(['store', 'product'])
            ->when(isset($filters['store_id']), fn ($q) => $q->where('store_id', $filters['store_id']))
            ->when($filters['search'] ?? null, fn ($q, $s) => $q->whereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$s}%")
                ->orWhere('sku', 'like', "%{$s}%")))
            ->when(isset($filters['low_stock']), fn ($q) => $q->whereRaw('on_shelf_quantity + warehouse_quantity < ?', [$filters['low_stock']]))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function getStoreInventory(int $storeId): \Illuminate\Database\Eloquent\Collection
    {
        return StoreInventory::with('product')
            ->where('store_id', $storeId)
            ->get();
    }

    public function updateInventoryCount(StoreInventory $inventory, array $data): StoreInventory
    {
        $inventory->update([
            'on_shelf_quantity' => $data['on_shelf_quantity'] ?? $inventory->on_shelf_quantity,
            'warehouse_quantity' => $data['warehouse_quantity'] ?? $inventory->warehouse_quantity,
            'last_counted_at' => now(),
        ]);
        return $inventory->fresh(['store', 'product']);
    }

    public function adjustInventory(
        int $tenantId,
        int $storeId,
        int $productId,
        int $quantity,
        StockMovementType $type,
        ?string $refType = null,
        ?int $refId = null,
        ?int $userId = null,
        ?string $notes = null,
    ): StoreInventory {
        $inventory = StoreInventory::firstOrCreate(
            ['store_id' => $storeId, 'product_id' => $productId],
            ['tenant_id' => $tenantId, 'on_shelf_quantity' => 0, 'warehouse_quantity' => 0],
        );

        $inventory->increment('on_shelf_quantity', $quantity);

        StockMovement::create([
            'tenant_id' => $tenantId,
            'store_id' => $storeId,
            'product_id' => $productId,
            'type' => $type->value,
            'quantity' => $quantity,
            'reference_type' => $refType,
            'reference_id' => $refId,
            'user_id' => $userId,
            'notes' => $notes,
        ]);

        return $inventory->fresh(['store', 'product']);
    }

    // ─── Stock Movements ──────────────────────────────────────

    public function listStockMovements(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return StockMovement::query()
            ->with(['store', 'product', 'user'])
            ->when(isset($filters['store_id']), fn ($q) => $q->where('store_id', $filters['store_id']))
            ->when(isset($filters['product_id']), fn ($q) => $q->where('product_id', $filters['product_id']))
            ->when($filters['type'] ?? null, fn ($q, $t) => $q->where('type', $t))
            ->when($filters['date_from'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
            ->when($filters['date_to'] ?? null, fn ($q, $d) => $q->whereDate('created_at', '<=', $d))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function recordStockMovement(array $data): StockMovement
    {
        return DB::transaction(function () use ($data) {
            $movement = StockMovement::create($data);

            // Adjust inventory based on movement type
            $inventory = StoreInventory::firstOrCreate(
                ['store_id' => $data['store_id'], 'product_id' => $data['product_id']],
                ['tenant_id' => $data['tenant_id'], 'on_shelf_quantity' => 0, 'warehouse_quantity' => 0],
            );

            $type = StockMovementType::from($data['type']);
            $qty = $data['quantity'];

            match ($type) {
                StockMovementType::StockIn => $inventory->increment('warehouse_quantity', $qty),
                StockMovementType::StockOut => $inventory->decrement('on_shelf_quantity', $qty),
                StockMovementType::SellOut,
                StockMovementType::SellThrough => $inventory->decrement('on_shelf_quantity', $qty),
                StockMovementType::Return => $inventory->increment('on_shelf_quantity', $qty),
                StockMovementType::Adjustment => $inventory->increment('on_shelf_quantity', $qty),
            };

            return $movement->fresh(['store', 'product', 'user']);
        });
    }

    // ─── Mobile ───────────────────────────────────────────────

    public function myTransactions(int $userId, ?string $type = null): \Illuminate\Database\Eloquent\Collection
    {
        return PosTransaction::with(['store', 'items.product', 'terminal'])
            ->where('user_id', $userId)
            ->when($type, fn ($q, $t) => $q->where('type', $t))
            ->latest()
            ->get();
    }
}
