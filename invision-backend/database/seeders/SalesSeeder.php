<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use App\Models\CreditAccount;
use App\Models\CreditTransaction;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Rebate;
use App\Models\SalesOrder;
use App\Models\SalesOrderItem;
use App\Models\Store;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;

class SalesSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'invision-default')->first();
        if (! $tenant) {
            return;
        }

        $admin = User::where('email', 'admin@invision.test')->first();
        $field = User::where('email', 'field@invision.test')->first();
        $stores = Store::where('tenant_id', $tenant->id)->take(3)->get();
        $products = Product::where('tenant_id', $tenant->id)->take(5)->get();

        if ($stores->isEmpty() || $products->isEmpty()) {
            return;
        }

        // ─── Rebates ─────────────────────────────────────
        $rebate1 = Rebate::firstOrCreate(
            ['name' => '10% Volume Discount', 'tenant_id' => $tenant->id],
            [
                'description' => 'Get 10% off when purchasing 10+ units',
                'type' => 'percentage',
                'value' => 10.00,
                'min_quantity' => 10,
                'max_quantity' => null,
                'start_date' => now()->subDays(30),
                'end_date' => now()->addDays(60),
                'is_active' => true,
                'product_id' => $products->first()?->id,
            ]
        );

        $rebate2 = Rebate::firstOrCreate(
            ['name' => 'Flat $5 Rebate', 'tenant_id' => $tenant->id],
            [
                'description' => 'Flat $5 off on selected products',
                'type' => 'fixed',
                'value' => 5.00,
                'min_quantity' => 1,
                'max_quantity' => null,
                'start_date' => now()->subDays(15),
                'end_date' => now()->addDays(45),
                'is_active' => true,
                'product_id' => $products->skip(1)->first()?->id,
            ]
        );

        // ─── Sales Orders ─────────────────────────────────
        $order1 = SalesOrder::firstOrCreate(
            ['order_number' => 'SO-SEED-0001'],
            [
                'tenant_id' => $tenant->id,
                'store_id' => $stores[0]->id,
                'user_id' => $field->id,
                'status' => OrderStatus::Delivered,
                'subtotal' => 500.00,
                'discount_amount' => 25.00,
                'tax_amount' => 0,
                'total_amount' => 475.00,
                'delivered_at' => now()->subDays(5),
            ]
        );

        if ($order1->wasRecentlyCreated && $products->count() >= 2) {
            SalesOrderItem::create([
                'sales_order_id' => $order1->id,
                'product_id' => $products[0]->id,
                'quantity' => 10,
                'unit_price' => 30.00,
                'discount_percent' => 5,
                'discount_amount' => 15.00,
                'line_total' => 285.00,
            ]);
            SalesOrderItem::create([
                'sales_order_id' => $order1->id,
                'product_id' => $products[1]->id,
                'quantity' => 5,
                'unit_price' => 40.00,
                'discount_percent' => 5,
                'discount_amount' => 10.00,
                'line_total' => 190.00,
            ]);

            // Payment for order1
            Payment::create([
                'tenant_id' => $tenant->id,
                'sales_order_id' => $order1->id,
                'user_id' => $field->id,
                'payment_method' => PaymentMethod::Cash,
                'amount' => 475.00,
                'status' => PaymentStatus::Paid,
                'paid_at' => now()->subDays(5),
            ]);
        }

        $order2 = SalesOrder::firstOrCreate(
            ['order_number' => 'SO-SEED-0002'],
            [
                'tenant_id' => $tenant->id,
                'store_id' => $stores->count() > 1 ? $stores[1]->id : $stores[0]->id,
                'user_id' => $field->id,
                'status' => OrderStatus::Confirmed,
                'subtotal' => 800.00,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 800.00,
            ]
        );

        if ($order2->wasRecentlyCreated && $products->count() >= 3) {
            SalesOrderItem::create([
                'sales_order_id' => $order2->id,
                'product_id' => $products[2]->id,
                'quantity' => 20,
                'unit_price' => 40.00,
                'discount_percent' => 0,
                'discount_amount' => 0,
                'line_total' => 800.00,
            ]);

            // Partial payment via check
            Payment::create([
                'tenant_id' => $tenant->id,
                'sales_order_id' => $order2->id,
                'user_id' => $field->id,
                'payment_method' => PaymentMethod::Check,
                'amount' => 500.00,
                'check_number' => 'CHK-12345',
                'check_date' => now()->addDays(7),
                'bank_name' => 'National Bank',
                'status' => PaymentStatus::Paid,
                'paid_at' => now()->subDays(2),
            ]);
        }

        $order3 = SalesOrder::firstOrCreate(
            ['order_number' => 'SO-SEED-0003'],
            [
                'tenant_id' => $tenant->id,
                'store_id' => $stores->count() > 2 ? $stores[2]->id : $stores[0]->id,
                'user_id' => $field->id,
                'status' => OrderStatus::Draft,
                'subtotal' => 250.00,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total_amount' => 250.00,
            ]
        );

        if ($order3->wasRecentlyCreated && $products->count() >= 1) {
            SalesOrderItem::create([
                'sales_order_id' => $order3->id,
                'product_id' => $products[0]->id,
                'quantity' => 5,
                'unit_price' => 50.00,
                'discount_percent' => 0,
                'discount_amount' => 0,
                'line_total' => 250.00,
            ]);
        }

        // ─── Credit Accounts ──────────────────────────────
        $creditAccount = CreditAccount::firstOrCreate(
            ['tenant_id' => $tenant->id, 'store_id' => $stores[0]->id],
            [
                'credit_limit' => 5000.00,
                'current_balance' => 475.00,
                'last_payment_at' => now()->subDays(10),
            ]
        );

        if ($creditAccount->wasRecentlyCreated) {
            CreditTransaction::create([
                'credit_account_id' => $creditAccount->id,
                'sales_order_id' => $order1->id,
                'type' => 'debit',
                'amount' => 475.00,
                'balance_after' => 475.00,
                'description' => 'Credit sale - Order #SO-SEED-0001',
            ]);
        }

        if ($stores->count() > 1) {
            CreditAccount::firstOrCreate(
                ['tenant_id' => $tenant->id, 'store_id' => $stores[1]->id],
                [
                    'credit_limit' => 3000.00,
                    'current_balance' => 0,
                ]
            );
        }
    }
}
