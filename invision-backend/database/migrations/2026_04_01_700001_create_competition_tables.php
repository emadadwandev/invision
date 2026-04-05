<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('competitors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('logo_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index(['tenant_id', 'is_active']);
        });

        Schema::create('competitor_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('competitor_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->string('barcode')->nullable();
            $table->string('category')->nullable();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('competitor_id');
            $table->index(['tenant_id', 'competitor_id']);
        });

        Schema::create('competitor_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_visit_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('competitor_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('competitor_product_id')->nullable()->constrained()->nullOnDelete();
            $table->string('observation_type')->default('sales');
            $table->integer('quantity')->nullable();
            $table->decimal('price', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->string('photo_path')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamp('observed_at')->nullable();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index(['tenant_id', 'store_id']);
            $table->index(['tenant_id', 'competitor_id']);
            $table->index(['tenant_id', 'observation_type']);
            $table->index('store_visit_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('competitor_observations');
        Schema::dropIfExists('competitor_products');
        Schema::dropIfExists('competitors');
    }
};
