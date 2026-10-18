<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('store_visit_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_visit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->foreignId('competitor_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('our_sell_out_quantity')->default(0);
            $table->unsignedInteger('competitor_sell_out_quantity')->default(0);
            $table->unsignedInteger('our_shelf_stock_quantity')->default(0);
            $table->unsignedInteger('competitor_shelf_stock_quantity')->default(0);
            $table->unsignedInteger('our_warehouse_stock_quantity')->default(0);
            $table->unsignedInteger('competitor_warehouse_stock_quantity')->default(0);
            $table->unsignedInteger('our_sell_through_quantity')->default(0);
            $table->unsignedInteger('competitor_sell_through_quantity')->default(0);
            $table->unsignedInteger('planned_sell_through_quantity')->default(0);
            $table->date('planned_sell_through_date')->nullable();
            $table->string('planned_sell_through_basis', 30)->nullable();
            $table->string('planned_sell_through_notes', 1000)->nullable();
            $table->boolean('our_posm_present')->default(false);
            $table->boolean('competitor_posm_present')->default(false);
            $table->boolean('our_front_shop_present')->default(false);
            $table->boolean('competitor_front_shop_present')->default(false);
            $table->timestamps();

            $table->index(['tenant_id', 'store_visit_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_visit_metrics');
    }
};
