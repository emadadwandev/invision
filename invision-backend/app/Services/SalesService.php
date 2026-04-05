<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\CreditAccount;
use App\Models\CreditTransaction;
use App\Models\DepositReceipt;
use App\Models\Payment;
use App\Models\Rebate;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SalesService
{
    // ─── Sales Orders ─────────────────────────────────────────

    public function listOrders(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = SalesOrder::with(['store', 'salesperson']);

        if (! empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('order_number', 'like', "%{$filters['search']}%")
                  ->orWhereHas('store', fn ($q2) => $q2->where('name', 'like', "%{$filters['search']}%"));
            });
        }

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['store_id'])) {
            $query->where('store_id', $filters['store_id']);
        }

        if (! empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function createOrder(array $data, array $items): SalesOrder
    {
        return DB::transaction(function () use ($data, $items) {
            $data['order_number'] = $this->generateOrderNumber();
            $data['status'] = OrderStatus::Draft;

            $order = SalesOrder::create($data);

            $subtotal = 0;
            $totalDiscount = 0;

            foreach ($items as $item) {
                $lineDiscount = ($item['unit_price'] * $item['quantity']) * (($item['discount_percent'] ?? 0) / 100);
                $lineTotal = ($item['unit_price'] * $item['quantity']) - $lineDiscount;

                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'discount_percent' => $item['discount_percent'] ?? 0,
                    'discount_amount' => $lineDiscount,
                    'line_total' => $lineTotal,
                    'barcode_scanned' => $item['barcode_scanned'] ?? null,
                ]);

                $subtotal += ($item['unit_price'] * $item['quantity']);
                $totalDiscount += $lineDiscount;
            }

            $taxAmount = ($subtotal - $totalDiscount) * 0; // Tax rate can be configured
            $totalAmount = $subtotal - $totalDiscount + $taxAmount;

            $order->update([
                'subtotal' => $subtotal,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
            ]);

            return $order->load(['store', 'salesperson', 'items.product']);
        });
    }

    public function updateOrder(SalesOrder $order, array $data): SalesOrder
    {
        $order->update($data);

        return $order->load(['store', 'salesperson', 'items.product']);
    }

    public function confirmOrder(SalesOrder $order): SalesOrder
    {
        $order->update(['status' => OrderStatus::Confirmed]);

        return $order;
    }

    public function deliverOrder(SalesOrder $order): SalesOrder
    {
        $order->update([
            'status' => OrderStatus::Delivered,
            'delivered_at' => now(),
        ]);

        return $order;
    }

    public function cancelOrder(SalesOrder $order): SalesOrder
    {
        $order->update(['status' => OrderStatus::Cancelled]);

        return $order;
    }

    public function deleteOrder(SalesOrder $order): void
    {
        $order->delete();
    }

    // ─── Order Items ──────────────────────────────────────────

    public function addOrderItem(SalesOrder $order, array $data): SalesOrderItem
    {
        $lineDiscount = ($data['unit_price'] * $data['quantity']) * (($data['discount_percent'] ?? 0) / 100);
        $lineTotal = ($data['unit_price'] * $data['quantity']) - $lineDiscount;

        $item = $order->items()->create([
            'product_id' => $data['product_id'],
            'quantity' => $data['quantity'],
            'unit_price' => $data['unit_price'],
            'discount_percent' => $data['discount_percent'] ?? 0,
            'discount_amount' => $lineDiscount,
            'line_total' => $lineTotal,
            'barcode_scanned' => $data['barcode_scanned'] ?? null,
        ]);

        $this->recalculateOrderTotals($order);

        return $item->load('product');
    }

    public function removeOrderItem(SalesOrderItem $item): void
    {
        $order = $item->salesOrder;
        $item->delete();
        $this->recalculateOrderTotals($order);
    }

    private function recalculateOrderTotals(SalesOrder $order): void
    {
        $order->refresh();
        $subtotal = $order->items()->sum(DB::raw('unit_price * quantity'));
        $totalDiscount = $order->items()->sum('discount_amount');
        $taxAmount = ($subtotal - $totalDiscount) * 0;
        $totalAmount = $subtotal - $totalDiscount + $taxAmount;

        $order->update([
            'subtotal' => $subtotal,
            'discount_amount' => $totalDiscount,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ]);
    }

    // ─── Payments ─────────────────────────────────────────────

    public function listPayments(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Payment::with(['salesOrder.store', 'collector']);

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['payment_method'])) {
            $query->where('payment_method', $filters['payment_method']);
        }

        if (! empty($filters['sales_order_id'])) {
            $query->where('sales_order_id', $filters['sales_order_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function recordPayment(array $data): Payment
    {
        return DB::transaction(function () use ($data) {
            $data['status'] = PaymentStatus::Paid;
            $data['paid_at'] = now();

            $payment = Payment::create($data);

            // If paid via credit, update credit account
            if ($payment->payment_method === \App\Enums\PaymentMethod::Credit) {
                $this->debitCredit(
                    $payment->salesOrder->store_id,
                    $payment->tenant_id,
                    $payment->amount,
                    $payment->sales_order_id,
                    $payment->id,
                    'Credit sale - Order #' . $payment->salesOrder->order_number
                );
            }

            return $payment->load(['salesOrder', 'collector']);
        });
    }

    // ─── Credit Management ────────────────────────────────────

    public function listCreditAccounts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = CreditAccount::with(['store']);

        if (! empty($filters['search'])) {
            $query->whereHas('store', fn ($q) => $q->where('name', 'like', "%{$filters['search']}%"));
        }

        return $query->latest()->paginate($perPage);
    }

    public function createCreditAccount(array $data): CreditAccount
    {
        return CreditAccount::create($data);
    }

    public function updateCreditAccount(CreditAccount $account, array $data): CreditAccount
    {
        $account->update($data);

        return $account;
    }

    public function debitCredit(int $storeId, int $tenantId, float $amount, ?int $orderId, ?int $paymentId, string $description): CreditTransaction
    {
        $account = CreditAccount::where('store_id', $storeId)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $newBalance = (float) $account->current_balance + $amount;

        $account->update(['current_balance' => $newBalance]);

        return CreditTransaction::create([
            'credit_account_id' => $account->id,
            'sales_order_id' => $orderId,
            'payment_id' => $paymentId,
            'type' => 'debit',
            'amount' => $amount,
            'balance_after' => $newBalance,
            'description' => $description,
        ]);
    }

    public function creditPayment(int $storeId, int $tenantId, float $amount, ?int $paymentId, string $description): CreditTransaction
    {
        $account = CreditAccount::where('store_id', $storeId)
            ->where('tenant_id', $tenantId)
            ->firstOrFail();

        $newBalance = (float) $account->current_balance - $amount;

        $account->update([
            'current_balance' => $newBalance,
            'last_payment_at' => now(),
        ]);

        return CreditTransaction::create([
            'credit_account_id' => $account->id,
            'payment_id' => $paymentId,
            'type' => 'credit',
            'amount' => $amount,
            'balance_after' => $newBalance,
            'description' => $description,
        ]);
    }

    // ─── Deposit Receipts ─────────────────────────────────────

    public function listDepositReceipts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = DepositReceipt::with(['payment.salesOrder', 'user']);

        if (! empty($filters['search'])) {
            $query->where('receipt_number', 'like', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function createDepositReceipt(array $data): DepositReceipt
    {
        $data['receipt_number'] = $this->generateReceiptNumber();

        return DepositReceipt::create($data)->load(['payment', 'user']);
    }

    // ─── Rebates ──────────────────────────────────────────────

    public function listRebates(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Rebate::with(['product', 'category']);

        if (! empty($filters['search'])) {
            $query->where('name', 'like', "%{$filters['search']}%");
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function createRebate(array $data): Rebate
    {
        return Rebate::create($data)->load(['product', 'category']);
    }

    public function updateRebate(Rebate $rebate, array $data): Rebate
    {
        $rebate->update($data);

        return $rebate->load(['product', 'category']);
    }

    public function deleteRebate(Rebate $rebate): void
    {
        $rebate->delete();
    }

    public function getApplicableRebates(int $productId, int $quantity): array
    {
        return Rebate::where('is_active', true)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->where(function ($q) use ($productId) {
                $q->where('product_id', $productId)
                  ->orWhereNull('product_id');
            })
            ->where('min_quantity', '<=', $quantity)
            ->where(function ($q) use ($quantity) {
                $q->whereNull('max_quantity')
                  ->orWhere('max_quantity', '>=', $quantity);
            })
            ->get()
            ->toArray();
    }

    // ─── Mobile: My Sales ─────────────────────────────────────

    public function myOrders(int $userId, ?string $status = null): LengthAwarePaginator
    {
        $query = SalesOrder::with(['store', 'items.product'])
            ->where('user_id', $userId);

        if ($status) {
            $query->where('status', $status);
        }

        return $query->latest()->paginate(15);
    }

    // ─── Helpers ──────────────────────────────────────────────

    private function generateOrderNumber(): string
    {
        do {
            $number = 'SO-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
        } while (SalesOrder::where('order_number', $number)->exists());

        return $number;
    }

    private function generateReceiptNumber(): string
    {
        do {
            $number = 'DR-' . now()->format('Ymd') . '-' . strtoupper(Str::random(4));
        } while (DepositReceipt::where('receipt_number', $number)->exists());

        return $number;
    }
}
