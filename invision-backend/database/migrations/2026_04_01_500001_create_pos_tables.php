<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // POS Terminals — registered devices at stores
        Schema::create('pos_terminals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->string('terminal_code')->unique();
            $table->string('name');
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'store_id']);
        });

        // POS Transactions — sell-out / sell-through / return records
        Schema::create('pos_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained();
            $table->foreignId('pos_terminal_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->constrained();
            $table->string('transaction_number')->unique();
            $table->string('type')->default('sell_out');
            $table->string('status')->default('pending');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'status']);
            $table->index('store_id');
            $table->index('user_id');
        });

        // POS Transaction Items — line items for each POS transaction
        Schema::create('pos_transaction_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pos_transaction_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->integer('quantity');
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('line_total', 12, 2);
            $table->string('barcode_scanned')->nullable();
            $table->timestamps();
        });

        // Store Inventory — current stock levels per product per store
        Schema::create('store_inventory', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->integer('on_shelf_quantity')->default(0);
            $table->integer('warehouse_quantity')->default(0);
            $table->timestamp('last_counted_at')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'product_id']);
            $table->index('tenant_id');
        });

        // Stock Movements — audit trail for all inventory changes
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained();
            $table->foreignId('product_id')->constrained();
            $table->string('type');
            $table->integer('quantity');
            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('user_id')->constrained();
            $table->timestamps();

            $table->index(['tenant_id', 'store_id']);
            $table->index(['tenant_id', 'product_id']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('store_inventory');
        Schema::dropIfExists('pos_transaction_items');
        Schema::dropIfExists('pos_transactions');
        Schema::dropIfExists('pos_terminals');
    }
};
