<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Route plans (a plan for a field rep's daily/weekly route)
        Schema::create('route_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->foreignId('assigned_to')->constrained('users')->cascadeOnDelete();
            $table->string('frequency')->default('daily'); // daily, weekly, bi_weekly, monthly
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->string('status')->default('draft');
            $table->unsignedInteger('total_stores')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'assigned_to']);
            $table->index(['tenant_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });

        // Stores within a route plan (ordered sequence)
        Schema::create('route_plan_stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('route_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('visit_order');
            $table->unsignedInteger('expected_duration_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['route_plan_id', 'store_id']);
            $table->index(['route_plan_id', 'visit_order']);
        });

        // Route instances (a specific day's execution of a route plan)
        Schema::create('route_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('route_plan_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('route_date');
            $table->string('status')->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->decimal('total_distance_km', 8, 2)->nullable();
            $table->unsignedInteger('total_visits')->default(0);
            $table->unsignedInteger('completed_visits')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['route_plan_id', 'user_id', 'route_date']);
            $table->index(['tenant_id', 'route_date']);
            $table->index(['user_id', 'route_date']);
        });

        // Store visits (check-in/out at each store)
        Schema::create('store_visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('route_instance_id')->constrained()->cascadeOnDelete();
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('visit_order');
            $table->string('status')->default('pending');

            // Check-in
            $table->timestamp('checked_in_at')->nullable();
            $table->decimal('checkin_latitude', 10, 8)->nullable();
            $table->decimal('checkin_longitude', 11, 8)->nullable();
            $table->string('checkin_qr_code')->nullable();
            $table->decimal('checkin_distance_meters', 8, 2)->nullable();

            // Check-out
            $table->timestamp('checked_out_at')->nullable();
            $table->decimal('checkout_latitude', 10, 8)->nullable();
            $table->decimal('checkout_longitude', 11, 8)->nullable();

            // Duration & notes
            $table->unsignedInteger('duration_minutes')->nullable();
            $table->text('notes')->nullable();
            $table->string('skip_reason')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'store_id']);
            $table->index(['route_instance_id', 'visit_order']);
            $table->index(['user_id', 'checked_in_at']);
        });

        // GPS tracking logs (continuous tracking during duty)
        Schema::create('gps_tracking_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('route_instance_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('accuracy_meters', 8, 2)->nullable();
            $table->decimal('speed_kmh', 6, 2)->nullable();
            $table->decimal('bearing', 6, 2)->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['user_id', 'recorded_at']);
            $table->index(['tenant_id', 'recorded_at']);
            $table->index(['route_instance_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gps_tracking_logs');
        Schema::dropIfExists('store_visits');
        Schema::dropIfExists('route_instances');
        Schema::dropIfExists('route_plan_stores');
        Schema::dropIfExists('route_plans');
    }
};
