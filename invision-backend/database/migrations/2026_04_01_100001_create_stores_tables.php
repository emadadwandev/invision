<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained('tenants')->cascadeOnDelete();
            $table->string('name');
            $table->string('code')->nullable();
            $table->string('qr_code')->nullable();
            $table->string('category')->default('other');
            $table->string('rank')->default('unranked');
            $table->decimal('gps_latitude', 10, 8)->nullable();
            $table->decimal('gps_longitude', 11, 8)->nullable();
            $table->text('address')->nullable();
            $table->foreignId('area_id')->nullable()->constrained('areas')->nullOnDelete();
            $table->json('profile')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index(['tenant_id', 'category']);
            $table->index(['tenant_id', 'area_id']);
            $table->index(['gps_latitude', 'gps_longitude']);
            $table->unique(['tenant_id', 'code']);
        });

        Schema::create('store_contacts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('name');
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('position')->nullable();
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('store_contacts');
        Schema::dropIfExists('stores');
    }
};
