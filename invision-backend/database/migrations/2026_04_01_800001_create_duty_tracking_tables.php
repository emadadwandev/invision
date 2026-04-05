<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Duty sessions — track when field force starts/stops duty
        Schema::create('duty_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->decimal('start_latitude', 10, 8)->nullable();
            $table->decimal('start_longitude', 11, 8)->nullable();
            $table->decimal('end_latitude', 10, 8)->nullable();
            $table->decimal('end_longitude', 11, 8)->nullable();
            $table->integer('total_minutes')->nullable();
            $table->integer('total_gps_logs')->default(0);
            $table->decimal('total_distance_km', 8, 2)->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id']);
            $table->index(['user_id', 'started_at']);
        });

        // Geo-fence settings — configurable per tenant
        Schema::create('geofence_settings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->unique();
            $table->integer('checkin_radius_meters')->default(50);
            $table->integer('checkout_radius_meters')->default(100);
            $table->boolean('enforce_geofence')->default(true);
            $table->integer('gps_tracking_interval_seconds')->default(30);
            $table->integer('gps_batch_size')->default(10);
            $table->boolean('require_gps_for_checkin')->default(true);
            $table->boolean('auto_checkout_on_leave')->default(false);
            $table->integer('auto_checkout_distance_meters')->default(200);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('geofence_settings');
        Schema::dropIfExists('duty_sessions');
    }
};
