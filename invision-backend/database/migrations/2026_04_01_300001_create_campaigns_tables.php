<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ─── Campaigns ────────────────────────────────────────
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->default('promotion');
            $table->string('status')->default('draft');
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('budget', 12, 2)->nullable();
            $table->decimal('spent', 12, 2)->default(0);
            $table->json('offer_details')->nullable();
            $table->json('reward_details')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'status']);
            $table->index(['start_date', 'end_date']);
            $table->index(['tenant_id', 'type']);
        });

        // ─── Campaign ↔ Store targeting ───────────────────────
        Schema::create('campaign_stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['campaign_id', 'store_id']);
        });

        // ─── Campaign ↔ Product targeting ─────────────────────
        Schema::create('campaign_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['campaign_id', 'product_id']);
        });

        // ─── Campaign tasks assigned to field users ───────────
        Schema::create('campaign_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('pending');
            $table->text('instructions')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['campaign_id', 'store_id']);
            $table->index(['assigned_to', 'status']);
        });

        // ─── Campaign task photos (proof of execution) ────────
        Schema::create('campaign_task_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_task_id')->constrained()->cascadeOnDelete();
            $table->string('photo_path');
            $table->string('caption')->nullable();
            $table->string('type')->default('proof');
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();
            $table->timestamps();

            $table->index('campaign_task_id');
        });

        // ─── Campaign entries (QR scans, barcodes, coupons) ───
        Schema::create('campaign_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_task_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('entry_type');
            $table->string('code')->nullable();
            $table->unsignedInteger('quantity')->default(1);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['campaign_id', 'store_id']);
            $table->index(['user_id', 'created_at']);
            $table->index('code');
        });

        // ─── POSM Materials (catalogue) ───────────────────────
        Schema::create('posm_materials', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('type')->nullable();
            $table->string('sku')->nullable();
            $table->unsignedInteger('quantity_available')->default(0);
            $table->string('image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'is_active']);
        });

        // ─── POSM placements at stores ────────────────────────
        Schema::create('posm_placements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('posm_material_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('placed_by')->constrained('users')->cascadeOnDelete();
            $table->date('placed_at');
            $table->string('condition')->default('good');
            $table->string('photo_path')->nullable();
            $table->date('last_checked_at')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tenant_id', 'store_id']);
            $table->index(['posm_material_id', 'condition']);
        });

        // ─── POSM condition check logs ────────────────────────
        Schema::create('posm_check_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('posm_placement_id')->constrained()->cascadeOnDelete();
            $table->foreignId('checked_by')->constrained('users')->cascadeOnDelete();
            $table->string('condition');
            $table->string('photo_path')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('replacement_requested')->default(false);
            $table->timestamps();

            $table->index(['posm_placement_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posm_check_logs');
        Schema::dropIfExists('posm_placements');
        Schema::dropIfExists('posm_materials');
        Schema::dropIfExists('campaign_entries');
        Schema::dropIfExists('campaign_task_photos');
        Schema::dropIfExists('campaign_tasks');
        Schema::dropIfExists('campaign_products');
        Schema::dropIfExists('campaign_stores');
        Schema::dropIfExists('campaigns');
    }
};
