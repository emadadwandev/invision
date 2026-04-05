<?php

namespace Database\Seeders;

use App\Enums\PosTransactionStatus;
use App\Enums\PosTransactionType;
use App\Enums\StockMovementType;
use App\Models\PosTerminal;
use App\Models\PosTransaction;
use App\Models\StockMovement;
use App\Models\StoreInventory;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class PosSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::first();
        if (! $tenant) {
            return;
        }

        $user = $tenant->users()->first();
        if (! $user) {
            return;
        }

        // POS Terminals
        $terminal1 = PosTerminal::firstOrCreate(
            ['terminal_code' => 'POS-001'],
            [
                'tenant_id' => $tenant->id,
                'store_id' => 1,
                'name' => 'Main Register',
                'is_active' => true,
            ],
        );

        $terminal2 = PosTerminal::firstOrCreate(
            ['terminal_code' => 'POS-002'],
            [
                'tenant_id' => $tenant->id,
                'store_id' => 2,
                'name' => 'Secondary Register',
                'is_active' => true,
            ],
        );

        // Store Inventory
        foreach ([1, 2] as $storeId) {
            foreach ([1, 2, 3] as $productId) {
                StoreInventory::firstOrCreate(
                    ['store_id' => $storeId, 'product_id' => $productId],
                    [
                        'tenant_id' => $tenant->id,
                        'on_shelf_quantity' => rand(10, 50),
                        'warehouse_quantity' => rand(20, 100),
                        'last_counted_at' => now(),
                    ],
                );
            }
        }

        // POS Transaction 1 — completed sell-out
        $txn1 = PosTransaction::firstOrCreate(
            ['transaction_number' => 'POS-20260401-0001'],
            [
                'tenant_id' => $tenant->id,
                'store_id' => 1,
                'pos_terminal_id' => $terminal1->id,
                'user_id' => $user->id,
                'type' => PosTransactionType::SellOut->value,
                'status' => PosTransactionStatus::Completed->value,
                'subtotal' => 150.00,
                'tax_amount' => 0,
                'total_amount' => 150.00,
                'payment_method' => 'cash',
            ],
        );

        if ($txn1->wasRecentlyCreated) {
            $txn1->items()->create([
                'product_id' => 1,
                'quantity' => 3,
                'unit_price' => 50.00,
                'discount_amount' => 0,
                'line_total' => 150.00,
            ]);
        }

        // POS Transaction 2 — pending sell-through
        $txn2 = PosTransaction::firstOrCreate(
            ['transaction_number' => 'POS-20260401-0002'],
            [
                'tenant_id' => $tenant->id,
                'store_id' => 2,
                'pos_terminal_id' => $terminal2->id,
                'user_id' => $user->id,
                'type' => PosTransactionType::SellThrough->value,
                'status' => PosTransactionStatus::Pending->value,
                'subtotal' => 200.00,
                'tax_amount' => 0,
                'total_amount' => 200.00,
                'payment_method' => 'card',
            ],
        );

        if ($txn2->wasRecentlyCreated) {
            $txn2->items()->create([
                'product_id' => 2,
                'quantity' => 2,
                'unit_price' => 100.00,
                'discount_amount' => 0,
                'line_total' => 200.00,
            ]);
        }

        // Stock Movements
        StockMovement::firstOrCreate(
            ['reference_type' => 'pos_seed', 'reference_id' => 1],
            [
                'tenant_id' => $tenant->id,
                'store_id' => 1,
                'product_id' => 1,
                'type' => StockMovementType::StockIn->value,
                'quantity' => 50,
                'user_id' => $user->id,
                'notes' => 'Initial stock delivery',
            ],
        );

        StockMovement::firstOrCreate(
            ['reference_type' => 'pos_seed', 'reference_id' => 2],
            [
                'tenant_id' => $tenant->id,
                'store_id' => 1,
                'product_id' => 1,
                'type' => StockMovementType::SellOut->value,
                'quantity' => -3,
                'user_id' => $user->id,
                'notes' => 'POS sell-out',
            ],
        );
    }
}
