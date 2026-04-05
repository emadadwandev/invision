<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Calendar weeks — configurable business weeks
        if (!Schema::hasTable('calendar_weeks')) {
            Schema::create('calendar_weeks', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->integer('year');
                $table->integer('week_number'); // 1-53
                $table->date('start_date');
                $table->date('end_date');
                $table->string('label', 60)->nullable(); // e.g., "W1 2026"
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->unique(['tenant_id', 'year', 'week_number']);
                $table->index('tenant_id');
            });
        }

        // Holidays
        if (!Schema::hasTable('holidays')) {
            Schema::create('holidays', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('name', 120);
                $table->date('date');
                $table->enum('type', ['public', 'company', 'regional'])->default('public');
                $table->text('description')->nullable();
                $table->boolean('is_recurring')->default(false); // repeats yearly
                $table->timestamps();

                $table->index(['tenant_id', 'date']);
            });
        }

        // Calendar events — campaigns, route deadlines, etc.
        if (!Schema::hasTable('calendar_events')) {
            Schema::create('calendar_events', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('title', 200);
                $table->text('description')->nullable();
                $table->dateTime('start_at');
                $table->dateTime('end_at')->nullable();
                $table->boolean('all_day')->default(false);
                $table->enum('type', ['campaign', 'route', 'meeting', 'deadline', 'other'])->default('other');
                $table->string('color', 7)->nullable(); // hex color
                $table->unsignedBigInteger('created_by')->nullable();
                $table->morphs('eventable'); // polymorphic: campaign, route_plan, etc.
                $table->timestamps();

                $table->index(['tenant_id', 'start_at', 'end_at']);
                $table->index(['tenant_id', 'type']);
            });
        }

        // Sales areas — LOB/product-based territory assignment
        if (!Schema::hasTable('sales_areas')) {
            Schema::create('sales_areas', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->string('name', 120);
                $table->text('description')->nullable();
                $table->unsignedBigInteger('parent_id')->nullable(); // hierarchy
                $table->unsignedBigInteger('manager_id')->nullable(); // assigned account manager
                $table->json('geometry')->nullable(); // GeoJSON polygon
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('parent_id')->references('id')->on('sales_areas')->nullOnDelete();
                $table->index('tenant_id');
                $table->index('manager_id');
            });
        }

        // Sales area assignments — which users/teams cover which areas
        if (!Schema::hasTable('sales_area_assignments')) {
            Schema::create('sales_area_assignments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id');
                $table->unsignedBigInteger('sales_area_id');
                $table->unsignedBigInteger('user_id');
                $table->string('role', 30)->default('representative'); // manager, team_leader, representative
                $table->json('product_lines')->nullable(); // specific LOBs/product categories
                $table->date('effective_from');
                $table->date('effective_to')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('sales_area_id')->references('id')->on('sales_areas')->cascadeOnDelete();
                $table->index(['tenant_id', 'user_id']);
                $table->index(['tenant_id', 'sales_area_id']);
            });
        }

        // Link stores to sales areas
        if (!Schema::hasTable('sales_area_stores')) {
            Schema::create('sales_area_stores', function (Blueprint $table) {
                $table->unsignedBigInteger('sales_area_id');
                $table->unsignedBigInteger('store_id');
                $table->primary(['sales_area_id', 'store_id']);
                $table->foreign('sales_area_id')->references('id')->on('sales_areas')->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_area_stores');
        Schema::dropIfExists('sales_area_assignments');
        Schema::dropIfExists('sales_areas');
        Schema::dropIfExists('calendar_events');
        Schema::dropIfExists('holidays');
        Schema::dropIfExists('calendar_weeks');
    }
};
