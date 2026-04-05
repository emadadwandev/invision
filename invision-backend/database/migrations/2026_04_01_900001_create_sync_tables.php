<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tracks what each user device has synced
        Schema::create('sync_tokens', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('user_id');
            $table->string('device_id', 100); // unique device identifier
            $table->timestamp('last_pulled_at')->nullable();
            $table->timestamp('last_pushed_at')->nullable();
            $table->unsignedInteger('pending_count')->default(0);
            $table->timestamps();

            $table->unique(['tenant_id', 'user_id', 'device_id']);
            $table->index('tenant_id');
        });

        // Queue of offline actions pushed from mobile
        Schema::create('sync_queue', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('user_id');
            $table->string('device_id', 100);
            $table->string('client_id', 100)->unique(); // idempotency key from client
            $table->string('entity_type', 60); // e.g. store_visit, gps_log, sales_order
            $table->string('action', 20); // create, update, delete
            $table->json('payload'); // the full action data
            $table->enum('status', ['pending', 'processed', 'failed', 'conflict'])->default('pending');
            $table->text('error_message')->nullable();
            $table->json('server_response')->nullable();
            $table->timestamp('client_timestamp'); // when the action was created offline
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['tenant_id', 'user_id', 'status']);
            $table->index(['tenant_id', 'device_id']);
            $table->index('client_timestamp');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sync_queue');
        Schema::dropIfExists('sync_tokens');
    }
};
