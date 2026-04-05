<?php

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Sales Orders ─────────────────────────────────────
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // salesperson
            $table->string('order_number')->unique();
            $table->string('status')->default(OrderStatus::Draft->value);
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['store_id']);
            $table->index(['user_id']);
        });

        // ─── Sales Order Items ────────────────────────────────
        Schema::create('sales_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('line_total', 12, 2);
            $table->string('barcode_scanned')->nullable();
            $table->timestamps();
        });

        // ─── Payments ─────────────────────────────────────────
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // collector
            $table->string('payment_method')->default(PaymentMethod::Cash->value);
            $table->decimal('amount', 12, 2);
            $table->string('reference_number')->nullable();
            $table->string('check_number')->nullable();
            $table->date('check_date')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('status')->default(PaymentStatus::Pending->value);
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['sales_order_id']);
        });

        // ─── Credit Accounts ──────────────────────────────────
        Schema::create('credit_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->decimal('credit_limit', 12, 2)->default(0);
            $table->decimal('current_balance', 12, 2)->default(0);
            $table->timestamp('last_payment_at')->nullable();
            $table->timestamps();

            $table->unique(['tenant_id', 'store_id']);
        });

        // ─── Credit Transactions ──────────────────────────────
        Schema::create('credit_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('credit_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sales_order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type'); // debit or credit
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // ─── Deposit Receipts ─────────────────────────────────
        Schema::create('deposit_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('receipt_number')->unique();
            $table->decimal('amount', 12, 2);
            $table->timestamp('deposited_at')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('branch')->nullable();
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // ─── Rebates ─────────────────────────────────────────
        Schema::create('rebates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('percentage'); // percentage, fixed, tiered
            $table->decimal('value', 10, 2)->default(0);
            $table->integer('min_quantity')->default(0);
            $table->integer('max_quantity')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->boolean('is_active')->default(true);
            $table->foreignId('product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rebates');
        Schema::dropIfExists('deposit_receipts');
        Schema::dropIfExists('credit_transactions');
        Schema::dropIfExists('credit_accounts');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('sales_order_items');
        Schema::dropIfExists('sales_orders');
    }
};
